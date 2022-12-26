<?php declare(strict_types=1);

namespace CourseHub\Controller;

use CourseHub\Common\Application\Auth\AuthHelper;
use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\CourseReader;
use CourseHub\Course\Application\CourseValidator;
use CourseHub\Course\Application\CourseWriter;
use CourseHub\Course\Application\Create\CreateCourse;
use CourseHub\Course\Application\Create\CreateCourseHandler;
use CourseHub\Course\Application\Update\UpdateCourse;
use CourseHub\Course\Application\Update\UpdateCourseHandler;
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
        private CourseValidator $courseValidator,
        private CourseReader $courseReader,
        private CourseWriter $courseWriter,
        private AuthHelper $authHelper
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

        return $this->render('course/list.html.twig', [
            'courses' => $this->courseReader->findAll(),
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
     * @Route("/course/run/{uuid}", methods={"GET"})
     */
    public function run($uuid): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }
        return $this->render('course/run.html.twig', [
            'course' => $this->courseReader->findById(RequiredUuid::fromString($uuid)),
            'ltiMessageHint' => json_encode([
                'launchid' => md5('elch' . time())
            ])
        ]);
    }
    /**
     * @Route("/course/edit/{uuid}", methods={"GET"})
     */
    public function editForm($uuid): Response
    {
        if(is_null($this->authHelper->getUser())) {
            $this->addFlash('errors', 'You must be logged in to perform this action!');
            return $this->redirectToRoute('login', array());
        }
        return $this->render('course/editing/form.html.twig', [
            'course' => $this->courseReader->findById(RequiredUuid::fromString($uuid)),
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

        return $this->redirectToRoute('course_list', array());
    }
    /**
     * @Route("/course/delete/{uuid}", methods={"GET"})
     */
    public function delete($uuid): Response
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