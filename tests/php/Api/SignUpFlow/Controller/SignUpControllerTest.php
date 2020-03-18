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

class SignUpControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $signUpAuthenticationRequest;
    private $signUpController;
    private $request;
    private $session;

    public function setUp(): void
    {
        $this->signUpAuthenticationRequest = m::mock(SignUpAuthenticationRequest::class);

        $this->signUpController = new SignUpController(
            $this->signUpAuthenticationRequest
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

        $this->createSignUpAuthenticationRequestExpectation([$requestBody], $authenticationRequest);
        $this->createSessionExpectation(['_signup_authentication_request_id', $authenticationRequestId]);

        $result = $this->signUpController->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(201, $result->getStatusCode());
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
