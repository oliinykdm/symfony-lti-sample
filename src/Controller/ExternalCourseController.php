<?php declare(strict_types=1);

namespace CourseHub\Controller;

use CourseHub\Common\Application\Auth\AuthHelper;
use CourseHub\Common\Domain\LtiSettings;
use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\CourseCompletionReader;
use CourseHub\Course\Application\CourseReader;
use CourseHub\Course\Application\CourseResourceReader;
use CourseHub\Course\Application\CourseResourceValidator;
use CourseHub\Course\Application\CourseResourceWriter;
use CourseHub\Course\Application\CourseValidator;
use CourseHub\Course\Application\CourseWriter;
use CourseHub\Course\Application\Create\CreateCourse;
use CourseHub\Course\Application\Create\CreateCourseHandler;
use CourseHub\Course\Application\Update\UpdateCourse;
use CourseHub\Course\Application\Update\UpdateCourseHandler;
use CourseHub\Course\Application\Update\UpdateCourseResource;
use CourseHub\Course\Application\Update\UpdateCourseResourceHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExternalCourseController extends AbstractController
{
    public function __construct(
        private CreateCourseHandler $createCourseHandler,
        private UpdateCourseHandler $updateCourseHandler,
        private UpdateCourseResourceHandler $updateCourseResourceHandler,
        private CourseResourceValidator $courseResourceValidator,
        private CourseResourceWriter $courseResourceWriter,
        private CourseValidator $courseValidator,
        private CourseReader $courseReader,
        private CourseWriter $courseWriter,
        private AuthHelper $authHelper,
        private CourseCompletionReader $courseCompletionReader,
        private CourseResourceReader $courseResourceReader,
    ) {}
    /**
     * @Route("/course/list", methods={"GET"}, name="course_list")
     */
    public function list(): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }

        if($this->authHelper->getUser()->getRole()->value() !== LtiSettings::MEMBERSHIP_CONTENTDEVELOPER) {
            $courses = $this->courseResourceReader->findAll();

            if(!is_null($courses)) {
                foreach($courses as &$course) {
                    $completion = $this->courseCompletionReader->findByCourseAndUserId(
                        RequiredUuid::fromString($course['uuid']),
                        RequiredUuid::fromString($this->authHelper->getUser()->getId()->value())
                    );;
                    $course['is_completed'] = (bool) $completion;
                    $course['completion_id'] = is_null($completion) ? null : $completion->getId();
                }
            }

            return $this->render('course/list_student.html.twig', [
                'courses' => $courses,
            ]);
        }

        $courses = $this->courseReader->findAll();

        return $this->render('course/list.html.twig', [
            'courses' => $courses,
        ]);
    }

    /**
     * @Route("/course/add", methods={"GET"})
     */
    public function addForm(): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }
        return $this->render('course/adding/form.html.twig', []);
    }
    /**
     * @Route("/course/deeplink/{uuid}", methods={"GET"}, name="deeplinkLaunch")
     */
    public function startDeepLink($uuid): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }

        return $this->render('course/start.html.twig', [
            'course' => $this->courseReader->findById(RequiredUuid::fromString($uuid)),
            'resource' => [],
            'ltiLoginHint' => 'LtiDeepLinkingRequest',
            'ltiMessageHint' => null,
        ]);
    }

    /**
     * @Route("/course/start/{type}/{uuid}", methods={"GET"}, name="start")
     */
    public function start($type, $uuid): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }

        $resource = $this->courseResourceReader->findById(RequiredUuid::fromString($uuid));

        return $this->render('course/start.html.twig', [
            'course' => $this->courseReader->findById($resource->getCourseId()),
            'resource' => $this->courseResourceReader->findById(RequiredUuid::fromString($uuid)),
            'ltiLoginHint' => $type,
            'ltiMessageHint' => json_encode([
                'internalResourceId' => $resource->getId()->value(),
                'resourceId' => $resource->getResourceId()->value(),
            ]),
        ]);
    }

    /**
     * @Route("/course/edit/{uuid}", methods={"GET"}, name="edit")
     */
    public function editForm($uuid): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }
        return $this->render('course/editing/form.html.twig', [
            'course' => $this->courseReader->findById(RequiredUuid::fromString($uuid)),
            'resources' => $this->courseResourceReader->findByCourseId(RequiredUuid::fromString($uuid)),
        ]);
    }
    /**
     * @Route("/course/edit/{uuid}", methods={"POST"})
     */
    public function editHandler(Request $request, $uuid): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }
        switch($request->get('action')) {
            case 'update_tool_data':
                $this->updateCourseHandler->handle(
                    new UpdateCourse(
                        $uuid,
                        $request->get('tool_name'),
                        $request->get('tool_url'),
                        $request->get('initiate_login_url'),
                        $request->get('jwks_url'),
                        $request->get('deep_linking_url'),
                    )
                );

                $response = new RedirectResponse('/course/edit/' . $uuid);
                if ($this->courseValidator->hasErrors()) {
                    foreach ($this->courseValidator->getErrors() as $errorMessage) {
                        $this->addFlash('errors', $errorMessage);
                    }
                    return $response;
                }

                $this->addFlash('success', 'Your course was successfully edited!');
                return $this->redirectToRoute('edit', array('uuid' => $uuid));
                break;
            case 'update_resource':
                $this->updateCourseResourceHandler->handle(
                    new UpdateCourseResource(
                        $request->get('resource_uuid'),
                        $request->get('title'),
                        $request->get('text'),
                        $request->get('resource_id'),
                    )
                );


                if ($this->courseResourceValidator->hasErrors()) {
                    foreach ($this->courseResourceValidator->getErrors() as $errorMessage) {
                        $this->addFlash('errors', $errorMessage);
                    }
                    return $this->redirectToRoute('edit', array('uuid' => $uuid, 'tab' => 'resources'));
                }

                $this->addFlash('success', 'Your resource was successfully edited!');
                return $this->redirectToRoute('edit', array('uuid' => $uuid, 'tab' => 'resources'));
                break;
        }

    }
    /**
     * @Route("/course/delete/resource/{uuid}/{courseId}", methods={"GET"}, name="delete_resource")
     */
    public function deleteResource($uuid, $courseId): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }
        $this->courseResourceWriter->delete(RequiredUuid::fromString($uuid));
        $this->addFlash('success', 'Your resource was successfully removed!');
        return $this->redirectToRoute('edit', array('uuid' => $courseId, 'tab' => 'resources'));
    }
    /**
     * @Route("/course/delete/{uuid}", methods={"GET"})
     */
    public function deleteCourse($uuid): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }
        $this->courseWriter->delete(RequiredUuid::fromString($uuid));
        $this->addFlash('success', 'Your course was successfully removed!');
        return $this->redirectToRoute('course_list', array());
    }
    /**
     * @Route("/course/add", methods={"POST"})
     */
    public function addHandler(Request $request): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }
        $this->createCourseHandler->handle(
            new CreateCourse(
                $request->get('tool_name'),
                $request->get('tool_url'),
                $request->get('initiate_login_url'),
                $request->get('jwks_url'),
                $request->get('deep_linking_url'),
            )
        );

        $response = new RedirectResponse('/course/add');
        if ($this->courseValidator->hasErrors()) {
            foreach ($this->courseValidator->getErrors() as $errorMessage) {
                $this->addFlash('errors', $errorMessage);
            }
            return $response;
        }

        $this->addFlash('success', 'Your course was successfully added!');

        return $this->redirectToRoute('course_list', array());

    }
}