<?php declare(strict_types=1);

namespace CourseHub\Controller;

use CourseHub\Common\Application\Auth\AuthHelper;
use CourseHub\Common\Domain\Jwks;
use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\CourseCompletionReader;
use CourseHub\Course\Application\CourseReader;
use CourseHub\Course\Application\CourseTokenReader;
use CourseHub\Course\Application\Create\CreateCompletion;
use CourseHub\Course\Application\Create\CreateCompletionHandler;
use Firebase\JWT\JWK;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Firebase\JWT\JWT;

class ApiController extends AbstractController
{
    public function __construct(
        private CourseReader $courseReader,
        private CourseTokenReader $courseTokenReader,
        private AuthHelper $authHelper,
        private CreateCompletionHandler $createCompletionHandler,
        private CourseCompletionReader $courseCompletionReader,
    ) {}
    /**
     * @Route("/api/lti/certs", methods={"GET"}, name="certs")
     */
    public function certs(): JsonResponse
    {
        $response = new JsonResponse(Jwks::getJwks(), Response::HTTP_OK);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }
    /**
     * @Route("/api/lti/token", methods={"POST"}, name="token")
     */
    public function token(Request $request): Response
    {
        if($request->getMethod() !== 'POST') {
            return new JsonResponse('incorrect_method', Response::HTTP_BAD_REQUEST);
        }

        if(!$request->get('client_assertion')
            || !$request->get('client_assertion_type')
            || !$request->get('grant_type')
            || !$request->get('scope')
        )
        {
            return new JsonResponse('not_enough_data', Response::HTTP_BAD_REQUEST);
        }
        if($request->get('client_assertion_type') !== 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer'
            || $request->get('grant_type') !== 'client_credentials')
        {
            return new JsonResponse('unsupported_grant_type', Response::HTTP_BAD_REQUEST);
        }

        $parts = explode('.', $request->get('client_assertion'));
        if (count($parts) !== 3) {
            return new JsonResponse('invalid_client_assertion', Response::HTTP_BAD_REQUEST);
        }

        $payload = JWT::urlsafeB64Decode($parts[1]);
        $claims = json_decode($payload, true);

        if(is_null($claims) || empty($claims['sub'])) {
            return new JsonResponse('invalid_sub', Response::HTTP_BAD_REQUEST);
        }

        $findCourseByClientId = $this->courseReader->findByClientId(RequiredUuid::fromString($claims['sub']));

        if(!$findCourseByClientId) {
            return new JsonResponse('invalid_client', Response::HTTP_BAD_REQUEST);
        }

        if ($findCourseByClientId->getJwksUrl()) {
            if (empty($findCourseByClientId->getJwksUrl()->value())) {
                return new JsonResponse('invalid_keyset', Response::HTTP_BAD_REQUEST);
            }
        }
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $keySet = file_get_contents($findCourseByClientId->getJwksUrl()->value(), false, stream_context_create($arrContextOptions));
        $keySetArr = json_decode($keySet, true);

        try {
            // JWK::parseKeySet uses RS256 algorithm by default.
            $keys = JWK::parseKeySet($keySetArr);
            JWT::decode($request->get('client_assertion'), $keys);
        } catch (\Exception $e) {
            return new JsonResponse('invalid_keyset', Response::HTTP_BAD_REQUEST);
        }


//        $scopes = array();
//        $requestedScopes = explode(' ', $request->get('scope'));
//        $permittedScopes = [];
//        $scopes = array_intersect($requestedScopes, $permittedScopes);

        /**
         * Check scopes later
         */

        $courseToken = $this->courseTokenReader->findByCourseId($findCourseByClientId->getId());

        if(!$courseToken) {
            return new JsonResponse('token_not_found', Response::HTTP_BAD_REQUEST);
        }

        $response = new JsonResponse([
            'access_token' => $courseToken->getToken()->value(),
            'token_type' => 'Bearer',
            'expires_in' => time() * 2,
            'scope' => ''
        ], Response::HTTP_OK);

        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }
    /**
     * @Route("/api/lti/completion-info/{completionId}", methods={"POST", "GET"}, name="completion_info")
     */
    public function completionInfo($completionId): JsonResponse
    {
        $completion = $this->courseCompletionReader->findById(
            RequiredUuid::fromString($completionId)
        );

        if(!$completion) {
            return new JsonResponse('invalid_completion_id', Response::HTTP_BAD_REQUEST);
        }

        $response = new JsonResponse(
            $completion->getDump()->value(), Response::HTTP_OK, [], true);

        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }
    /**
     * @Route("/api/lti/ags/{courseId}/scores", methods={"POST", "GET"}, name="ags")
     */
    public function ags($courseId, Request $request): Response
    {

        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }

        if(!$parametersAsArray) {
            return new JsonResponse('empty_data', Response::HTTP_BAD_REQUEST);
        }

        $this->createCompletionHandler->handle(

            new CreateCompletion(
                $parametersAsArray['userId'],
                $courseId,
                $parametersAsArray['scoreGiven'],
                $parametersAsArray['timestamp'],
                $request->getContent(),
            )
        );

        return new JsonResponse([
            'id' => $courseId,
            'status'=> 'success'
        ], Response::HTTP_OK);
    }
    /**
     * @Route("/api/lti/auth", methods={"POST", "GET"}, name="auth")
     */
    public function auth(Request $request): Response
    {

        if(!$request->get('scope')
            || !$request->get('response_type')
            || !$request->get('client_id')
            || !$request->get('redirect_uri')
            || !$request->get('login_hint')
            || !$request->get('nonce')
        )
        {
            return new JsonResponse('bad_request', Response::HTTP_BAD_REQUEST);
        }

        $ltiMessageHint = json_decode($request->get('lti_message_hint'));

        if(!isset($ltiMessageHint->launchid)) {
            return new JsonResponse('no_launch_id', Response::HTTP_BAD_REQUEST);
        }

        if($request->get('scope') !== 'openid') {
            return new JsonResponse('invalid_scope', Response::HTTP_BAD_REQUEST);
        }

        if($request->get('response_type') !== 'id_token') {
            return new JsonResponse('unsupported_response_type', Response::HTTP_BAD_REQUEST);
        }

        $course = $this->courseReader->findByClientId(RequiredUuid::fromString($request->get('client_id')));

        if(!$course) {
            return new JsonResponse('course_not_found', Response::HTTP_BAD_REQUEST);
        }

        $payload = [
            "iss" => $request->getSchemeAndHttpHost(),
            "aud" => [$course->getClientId()->value()],
            "sub" => $this->authHelper->getUser()->getId()->value(),
            "exp" => time() + 600,
            "iat" => time(),
            "nonce" => $request->get('nonce'),

            "https://purl.imsglobal.org/spec/lti/claim/deployment_id" => $course->getDeploymentId()->value(),
            "https://purl.imsglobal.org/spec/lti/claim/message_type" => "LtiResourceLinkRequest",
            "https://purl.imsglobal.org/spec/lti/claim/version" => "1.3.0",
            "https://purl.imsglobal.org/spec/lti/claim/target_link_uri" => $course->getToolUrl()->value(),
            "https://purl.imsglobal.org/spec/lti/claim/roles" => [
                $this->authHelper->getUser()->getRole()->value()
            ],
            "https://purl.imsglobal.org/spec/lti/claim/resource_link" => [
                "id" => $course->getId()->value(),
            ],
            "https://purl.imsglobal.org/spec/lti-ags/claim/endpoint" => [
                "scope" => [
                    "https://purl.imsglobal.org/spec/lti-ags/scope/lineitem",
                    "https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly",
                    "https://purl.imsglobal.org/spec/lti-ags/scope/score",
                ],
                "lineitem" => $request->getSchemeAndHttpHost() . '/api/lti/ags/' . $course->getId()->value()
            ],
            'https://purl.imsglobal.org/spec/lti/claim/custom' => [
                'id' => 'c49988d8-4fe4-447c-a3d4-6d48e61028ea'
            ]
        ];

        $jwt = JWT::encode($payload, Jwks::getPrivateKey()['key'], 'RS256', Jwks::getPrivateKey()['kid']);
        $params['id_token'] = $jwt;
        $params['state'] = $request->get('state');

        return $this->render('course/auth.html.twig', [
            'params' => $params,
            'redirect_uri' => $request->get('redirect_uri'),
        ]);

    }
}