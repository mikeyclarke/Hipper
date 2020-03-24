<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\App\Controller\Organization\Join;

use Hipper\Api\App\Controller\Organization\Join\JoinOrganizationController;
use Hipper\Organization\OrganizationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationRequest;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JoinOrganizationControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $signUpAuthenticationRequest;
    private $router;
    private $controller;
    private $request;
    private $session;

    public function setUp(): void
    {
        $this->signUpAuthenticationRequest = m::mock(SignUpAuthenticationRequest::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->controller = new JoinOrganizationController(
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
        $name = 'Mikey Clarke';
        $password = 'p455w0rd';
        $requestBody = [
            'email_domain' => 'usehipper.com',
            'email_local_part' => 'mikey',
            'name' => $name,
            'password' => $password,
            'terms_agreed' => true,
        ];
        $this->request->request->add($requestBody);
        $organizationSubdomain = 'acme';
        $organization = OrganizationModel::createFromArray([
            'subdomain' => $organizationSubdomain,
            'approved_email_domain_signup_allowed' => true,
        ]);
        $this->request->attributes->set('organization', $organization);

        $input = [
            'name' => $name,
            'password' => $password,
            'terms_agreed' => true,
            'email_address' => 'mikey@usehipper.com',
        ];
        $authenticationRequestId = 'auth-req-uuid';
        $authenticationRequest = SignUpAuthenticationModel::createFromArray([
            'id' => $authenticationRequestId,
        ]);
        $routerArgs = ['front_end.app.organization.join.verify_email', ['subdomain' => $organizationSubdomain]];
        $url = '/join/verify-email-address';
        $this->createSignUpAuthenticationRequestExpectation(
            [$input, $organization, ['approved_email_domain']],
            $authenticationRequest
        );
        $this->createSessionExpectation(['_signup_authentication_request_id', $authenticationRequestId]);
        $this->createRouterExpectation($routerArgs, $url);

        $result = $this->controller->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(201, $result->getStatusCode());
        $this->assertEquals($url, json_decode($result->getContent(), true)['url']);
    }

    /**
     * @test
     */
    public function cannotJoinIfApprovedEmailDomainSignupNotAllowed()
    {
        $name = 'Mikey Clarke';
        $password = 'p455w0rd';
        $requestBody = [
            'email_domain' => 'usehipper.com',
            'email_local_part' => 'mikey',
            'name' => $name,
            'password' => $password,
            'terms_agreed' => true,
        ];
        $this->request->request->add($requestBody);
        $organizationSubdomain = 'acme';
        $organization = OrganizationModel::createFromArray([
            'subdomain' => $organizationSubdomain,
            'approved_email_domain_signup_allowed' => false,
        ]);
        $this->request->attributes->set('organization', $organization);

        $result = $this->controller->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(403, $result->getStatusCode());
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
