<?php declare(strict_types=1);

namespace CourseHub\Controller;

use CourseHub\Common\Application\Auth\AuthHelper;
use CourseHub\Common\Domain\Jwks;
use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\CourseCompletionReader;
use CourseHub\Course\Application\CourseReader;
use CourseHub\Course\Application\CourseResourceReader;
use CourseHub\Course\Application\CourseResourceWriter;
use CourseHub\Course\Application\CourseTokenReader;
use CourseHub\Course\Application\Create\CreateCompletion;
use CourseHub\Course\Application\Create\CreateCompletionHandler;
use CourseHub\Course\Application\Create\CreateCourseResource;
use CourseHub\Course\Application\Create\CreateCourseResourceHandler;
use CourseHub\Course\Application\Update\UpdateCourse;
use CourseHub\Course\Application\Update\UpdateCourseHandler;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
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
        private CreateCourseResourceHandler $createCourseResourceHandler,
        private CourseResourceReader $courseResourceReader,
        private UpdateCourseHandler $updateCourseHandler
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
     * @Route("/api/lti/course-info/{courseId}", methods={"POST", "GET"}, name="course_info")
     */
    public function courseInfo($courseId): JsonResponse
    {
        $course = $this->courseReader->findById(
            RequiredUuid::fromString($courseId)
        );

        if(!$course) {
            return new JsonResponse('invalid_course_id', Response::HTTP_BAD_REQUEST);
        }

        $response = new JsonResponse(
            $course->getDump()->value(), Response::HTTP_OK, [], true);

        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }
    /**
     * @Route("/api/lti/resource-info/{resourceId}", methods={"POST", "GET"}, name="resource_info")
     */
    public function resourceInfo($resourceId): JsonResponse
    {
        $resource = $this->courseResourceReader->findById(
            RequiredUuid::fromString($resourceId)
        );

        if(!$resource) {
            return new JsonResponse('invalid_resource_id', Response::HTTP_BAD_REQUEST);
        }

        $response = new JsonResponse(
            $resource->getDump()->value(), Response::HTTP_OK, [], true);

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
     * @Route("/api/lti/ags/{resourceId}/scores", methods={"POST", "GET"}, name="ags")
     */
    public function ags($resourceId, Request $request): Response
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
                $resourceId,
                $parametersAsArray['scoreGiven'],
                $parametersAsArray['timestamp'],
                $request->getContent(),
            )
        );

        return new JsonResponse([
            'id' => $resourceId,
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

        $ltiMessageHint = $request->get('lti_message_hint');

        if(!is_null($ltiMessageHint)) {
            $ltiMessageHint = json_decode($ltiMessageHint, true);
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

        $ltiLoginHint = $request->get('login_hint');

        switch($ltiLoginHint) {
            case 'LtiResourceLinkRequest':
                $payload = [
                    "iss" => $request->getSchemeAndHttpHost(),
                    "aud" => [$course->getClientId()->value()],
                    "sub" => $this->authHelper->getUser()->getId()->value(),
                    "exp" => time() + 600,
                    "iat" => time(),
                    "nonce" => $request->get('nonce'),

                    "https://purl.imsglobal.org/spec/lti/claim/deployment_id" => $course->getDeploymentId()->value(),
                    "https://purl.imsglobal.org/spec/lti/claim/message_type" => $ltiLoginHint,
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
                        "lineitem" => $request->getSchemeAndHttpHost() . '/api/lti/ags/' . $ltiMessageHint['internalResourceId']
                    ],
                    'https://purl.imsglobal.org/spec/lti/claim/custom' => [
                        'id' => $ltiMessageHint['resourceId']
                    ]
                ];
                break;
            case 'LtiDeepLinkingRequest':
                $payload = [
                    "iss" => $request->getSchemeAndHttpHost(),
                    "aud" => [$course->getClientId()->value()],
                    "sub" => $this->authHelper->getUser()->getId()->value(),
                    "exp" => time() + 600,
                    "iat" => time(),
                    "nonce" => $request->get('nonce'),

                    "https://purl.imsglobal.org/spec/lti/claim/deployment_id" => $course->getDeploymentId()->value(),
                    "https://purl.imsglobal.org/spec/lti/claim/message_type" => $ltiLoginHint,
                    "https://purl.imsglobal.org/spec/lti/claim/version" => "1.3.0",
                    "https://purl.imsglobal.org/spec/lti/claim/target_link_uri" => $course->getDeepLinkingUrl()->value(),
                    "https://purl.imsglobal.org/spec/lti-dl/claim/deep_linking_settings" =>
                        [
                            'deep_link_return_url' => $request->getSchemeAndHttpHost() . '/api/lti/deeplink/' . $course->getId()->value(),
                            'accept_types' => ['ltiResourceLink'],
                            'accept_presentation_document_targets' => ["iframe", "window", "embed"],
                        ],
                    "https://purl.imsglobal.org/spec/lti/claim/roles" => [
                        $this->authHelper->getUser()->getRole()->value()
                    ],

                ];
                break;
        }



        $jwt = JWT::encode($payload, Jwks::getPrivateKey()['key'], 'RS256', Jwks::getPrivateKey()['kid']);
        $params['id_token'] = $jwt;
        $params['state'] = $request->get('state');

        return $this->render('course/auth.html.twig', [
            'params' => $params,
            'redirect_uri' => $request->get('redirect_uri'),
        ]);

    }

    /**
     * @Route("/api/lti/tool-registration/{courseId}", methods={"POST", "GET"}, name="tool-registration")
     */
    public function toolRegistration($courseId, Request $request): Response
    {
        $course = $this->courseReader->findById(RequiredUuid::fromString($courseId));
        $parametersAsArray = [];
        if ($content = $request->getContent()) {
            $parametersAsArray = json_decode($content, true);
        }
        $config = $parametersAsArray['https://purl.imsglobal.org/spec/lti-tool-configuration'];
        $this->updateCourseHandler->handle(
            new UpdateCourse(
                $courseId,
                $parametersAsArray['client_name'],
                $config['target_link_uri'],
                $parametersAsArray['initiate_login_uri'],
                $parametersAsArray['jwks_uri'],
                $config['messages'][0]['target_link_uri'], // deeplink url
                $content,
            )
        );

            $payload = [
                'client_id' => $course->getClientId()->value(),
                // 'registration_client_uri' => '',
                'application_type' => 'web',
                'response_types' => ['id_token'],
                'grant_types' => ['implict', 'client_credentials'],
                'initiate_login_uri' => $course->getInitiateLoginUrl()->value(),
                'redirect_uris' => [
                    $course->getToolUrl()->value(),
                    $course->getDeepLinkingUrl()->value()
                ],
                'client_name' => 'easylearn',
                'jwks_uri' => $course->getJwksUrl()->value(),
                'logo_uri' => '',
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => [],
                'scope' => 'https://purl.imsglobal.org/spec/lti-ags/scope/score',
                'https://purl.imsglobal.org/spec/lti-tool-configuration' => [
                    'domain' => $config['domain'],
                    'target_link_uri' => $course->getToolUrl()->value(),
                    'deployment_id' => $course->getDeploymentId()->value(),
                    'custom_parameters' => [

                    ],
                    'claims' => [
                        'iss', 'sub'
                    ],
                    'messages' => [
                        [
                            'type' => 'LtiDeepLinkingRequest',
                            'target_link_uri' => $course->getDeepLinkingUrl()->value(),
                            'label' => $course->getToolName()->value(),
                        ],
                        [
                            'type' => 'LtiResourceLinkRequest',
                            'target_link_uri' => $course->getToolUrl()->value(),
                            'label' => $course->getToolName()->value(),
                        ],
                    ],
                ]
                ];

        $response = new JsonResponse($payload, Response::HTTP_OK);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }

    /**
     * @Route("/api/lti/openid-configuration/{courseId}", methods={"POST", "GET"}, name="openid-registration")
     */
    public function openIdConfiguration($courseId, Request $request): Response
    {
        $course = $this->courseReader->findById(RequiredUuid::fromString($courseId));
        if($course) {
            $scopes[] = 'openid';
            $payload = [
                'issuer' => $request->getSchemeAndHttpHost(),
                'token_endpoint' => $request->getSchemeAndHttpHost() . '/api/lti/token',
                'token_endpoint_auth_methods_supported' => ['private_key_jwt'],
                'token_endpoint_auth_signing_alg_values_supported' => ['RS256'],
                'jwks_uri' => $request->getSchemeAndHttpHost() . '/api/lti/certs',
                'authorization_endpoint' => $request->getSchemeAndHttpHost() . '/api/lti/auth',
                'registration_endpoint' => $request->getSchemeAndHttpHost() . '/api/lti/tool-registration/' . $courseId,
                'scopes_supported' => $scopes,
                'response_types_supported' => ['id_token'],
                'subject_types_supported' => ['public', 'pairwise'],
                'id_token_signing_alg_values_supported' => ['RS256'],
                'claims_supported' => ['sub', 'iss', 'name', 'given_name', 'family_name', 'email'],
                'https://purl.imsglobal.org/spec/lti-platform-configuration' => [
                    'product_family_code' => 'elch',
                    'version' => '6.5',
                    'messages_supported' => [
                        ['type' => 'LtiResourceLinkRequest'],
                        ['type' => 'LtiDeepLinkingRequest', 'placements' => ['ContentArea']]
                    ],
                    // 'variables' => array_keys()
                    // capabilities of platform
                ]
            ];
        }
        else {
            $payload = 'bad_course';
        }
        $response = new JsonResponse($payload, Response::HTTP_OK);
        $response->setEncodingOptions($response->getEncodingOptions() | JSON_PRETTY_PRINT);
        return $response;
    }


    /**
     * @Route("/api/lti/deeplink/{courseId}", methods={"POST", "GET"}, name="deeplink")
     */
    public function deepLink($courseId, Request $request): Response
    {

        if(!$request->get('JWT')) {
            return new JsonResponse('bad_request', Response::HTTP_BAD_REQUEST);
        }

        $course = $this->courseReader->findById(RequiredUuid::fromString($courseId));

        if(!$course) {
            return new JsonResponse('invalid_course', Response::HTTP_BAD_REQUEST);
        }
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        $keySet = file_get_contents($course->getJwksUrl()->value(), false, stream_context_create($arrContextOptions));
        $keySetArr = json_decode($keySet, true);

        $keys = JWK::parseKeySet($keySetArr);
        $data = JWT::decode($request->get('JWT'), $keys);
        $contentItems = 'https://purl.imsglobal.org/spec/lti-dl/claim/content_items';
        $resources = $data->{$contentItems};

        foreach($resources as $resource) {
            $this->createCourseResourceHandler->handle(
              new CreateCourseResource(
                  $courseId,
                  $resource->type,
                  $resource->title ?? '',
                  $resource->text ?? '',
                  $resource->url,
                  $resource->custom->id,
                  json_encode($resource),
              )
            );
        }


       return $this->redirectToRoute('edit', array('uuid' => $courseId, 'tab' => 'resources'));

    }
}

