<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\App\Controller\Organization;

use Hipper\Api\App\Controller\Organization\LoginController;
use Hipper\Login\Login;
use Hipper\Security\UntrustedInternalUriRedirector;
use Hipper\Organization\OrganizationModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $login;
    private $untrustedInternalUriRedirector;
    private $controller;

    public function setUp(): void
    {
        $this->login = m::Mock(Login::class);
        $this->untrustedInternalUriRedirector = m::mock(UntrustedInternalUriRedirector::class);

        $this->controller = new LoginController(
            $this->login,
            $this->untrustedInternalUriRedirector
        );
    }

    /**
     * @test
     */
    public function postAction()
    {
        $requestBody = [
            'email_address' => 'hello@example.com',
            'password' => 'my s3cur3 p455w0rd',
            'redirect' => null,
        ];
        $organization = new OrganizationModel;
        $requestAttributes = [
            'organization' => $organization,
        ];
        $request = new Request([], $requestBody, $requestAttributes);
        $session = m::mock(SessionInterface::class);
        $request->setSession($session);

        $requestBodyOmittingRedirect = [
            'email_address' => $requestBody['email_address'],
            'password' => $requestBody['password'],
        ];
        $successUrl = '/';

        $this->createLoginExpectation([$organization, $requestBodyOmittingRedirect, $session]);
        $this->createUntrustedInternalUriRedirectorExpectation([$request->request->get('redirect'), '/'], $successUrl);

        $result = $this->controller->postAction($request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals($successUrl, json_decode($result->getContent(), true)['url']);
    }

    private function createUntrustedInternalUriRedirectorExpectation($args, $result)
    {
        $this->untrustedInternalUriRedirector
            ->shouldReceive('generateUri')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createLoginExpectation($args)
    {
        $this->login
            ->shouldReceive('login')
            ->once()
            ->with(...$args);
    }
}
