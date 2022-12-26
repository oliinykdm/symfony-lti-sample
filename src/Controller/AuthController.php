<?php declare(strict_types=1);

namespace CourseHub\Controller;

use CourseHub\Common\Application\Auth\Auth;
use CourseHub\Common\Application\Auth\AuthHandler;
use CourseHub\Common\Application\Auth\AuthHelper;
use CourseHub\Common\Application\Auth\User;
use CourseHub\Common\Application\Auth\UserReader;
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

class AuthController extends AbstractController
{
    public function __construct(
        private AuthHelper $authHelper,
    ) {}
    /**
     * @Route("/login", methods={"GET"}, name="login")
     */
    public function login(): Response
    {
        return $this->render('login/form.html.twig', [

        ]);
    }

    /**
     * @Route("/logout", methods={"GET"}, name="logout")
     */
    public function logout(): Response
    {
        $this->authHelper->logout();
        $this->addFlash('success', 'You have been successfully logged out!');
        return $this->redirectToRoute('login', array());
    }

    /**
     * @Route("/login", methods={"POST"})
     */
    public function loginHandler(Request $request): Response
    {
        $try = $this->authHelper->login($request->get('login'), $request->get('password'));

        if($try) {
            $this->addFlash('success', 'You have been successfully authorized!');
            return $this->redirectToRoute('course_list', array());
        }
        else {
            $this->addFlash('errors', 'Invalid login or password!');
        }

        return $this->redirectToRoute('login', array());
    }

}