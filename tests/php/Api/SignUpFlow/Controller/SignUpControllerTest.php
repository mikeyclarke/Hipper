<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\SignUpFlow\Controller;

use Hipper\Api\SignUpFlow\Controller\SignUpController;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationRequest;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SignUpControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $signUpAuthenticationRequest;
    private $router;
    private $signUpController;
    private $request;
    private $session;

    public function setUp(): void
    {
        $this->signUpAuthenticationRequest = m::mock(SignUpAuthenticationRequest::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->signUpController = new SignUpController(
            $this->signUpAuthenticationRequest,
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

        $authenticationRequestId = 'auth-req-uuid';
        $authenticationRequest = SignUpAuthenticationModel::createFromArray([
            'id' => $authenticationRequestId,
            'name' => 'James Holden',
            'email_address' => 'jh@example.com',
            'password' => 'p455w0rd',
        ]);
        $routeName = 'front_end.sign_up_flow.verify_email_address';
        $url = '/sign-up/verify-email-address';

        $this->createSignUpAuthenticationRequestExpectation([$requestBody], $authenticationRequest);
        $this->createSessionExpectation(['_signup_authentication_request_id', $authenticationRequestId]);
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

    private function createSignUpAuthenticationRequestExpectation($args, $result)
    {
        $this->signUpAuthenticationRequest
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
