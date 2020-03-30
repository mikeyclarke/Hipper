<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\SignUpFlow\Controller;

use Hipper\Api\SignUpFlow\Controller\SignUpController;
use Hipper\SignUp\AuthorizationStrategy\FoundingMemberSignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SignUpControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $signUpAuthorization;
    private $router;
    private $signUpController;
    private $request;
    private $session;

    public function setUp(): void
    {
        $this->signUpAuthorization = m::mock(FoundingMemberSignUpAuthorization::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->signUpController = new SignUpController(
            $this->signUpAuthorization,
            $this->router
        );

        $this->request = new Request();
        $this->session = m::mock(SessionInterface::class);
        $this->request->setSession($this->session);
    }

    /**
     * @test
     */
    public function postAction()
    {
        $requestBody = [
            'name' => 'James Holden',
            'email_address' => 'jh@example.com',
            'password' => 'p455w0rd',
        ];
        $this->request->request->add($requestBody);

        $authorizationRequestId = 'auth-req-uuid';
        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'id' => $authorizationRequestId,
        ]);
        $routeName = 'front_end.sign_up_flow.verify_email_address';
        $url = '/sign-up/verify-email-address';

        $this->createSignUpAuthorizationExpectation([$requestBody], $authorizationRequest);
        $this->createSessionExpectation(['_signup_authorization_request_id', $authorizationRequestId]);
        $this->createRouterExpectation([$routeName], $url);

        $result = $this->signUpController->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertIsArray(json_decode($result->getContent(), true));
        $this->assertEquals($url, json_decode($result->getContent(), true)['url']);
    }

    private function createRouterExpectation($args, $result)
    {
        $this->router
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSessionExpectation($args)
    {
        $this->session
            ->shouldReceive('set')
            ->once()
            ->with(...$args);
    }

    private function createSignUpAuthorizationExpectation($args, $result)
    {
        $this->signUpAuthorization
            ->shouldReceive('request')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
