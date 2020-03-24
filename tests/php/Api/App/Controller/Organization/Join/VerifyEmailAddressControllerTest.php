<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\App\Controller\Organization\Join;

use Hipper\Api\App\Controller\Organization\Join\VerifyEmailAddressController;
use Hipper\Login\Login;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\CreationStrategy\CreateFromApprovedEmailDomain;
use Hipper\Person\PersonModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\VerifySignUpAuthentication;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VerifyEmailAddressControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $createFromApprovedEmailDomain;
    private $login;
    private $router;
    private $verifySignUpAuthentication;
    private $controller;
    private $request;
    private $session;

    public function setUp(): void
    {
        $this->createFromApprovedEmailDomain = m::mock(CreateFromApprovedEmailDomain::class);
        $this->login = m::mock(Login::class);
        $this->router = m::mock(UrlGeneratorInterface::class);
        $this->verifySignUpAuthentication = m::mock(VerifySignUpAuthentication::class);

        $this->controller = new VerifyEmailAddressController(
            $this->createFromApprovedEmailDomain,
            $this->login,
            $this->router,
            $this->verifySignUpAuthentication
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
        $organizationId = 'org-uuid';
        $organizationSubdomain = 'acme';
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
            'subdomain' => $organizationSubdomain,
        ]);
        $this->request->attributes->set('organization', $organization);
        $verificationPhrase = 'foo bar baz qux';
        $this->request->request->set('phrase', $verificationPhrase);

        $authenticationRequestId = 'signup-auth-uuid';
        $authenticationRequest = SignUpAuthenticationModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $person = new PersonModel;
        $routerArgs = ['front_end.app.organization.home', ['subdomain' => $organizationSubdomain]];
        $url = '/';

        $this->createSessionExpectation(['_signup_authentication_request_id'], $authenticationRequestId);
        $this->createVerifySignUpAuthenticationExpectation(
            [$authenticationRequestId, $verificationPhrase],
            $authenticationRequest
        );
        $this->createCreateFromApprovedEmailDomainExpectation([$organization, $authenticationRequest], $person);
        $this->createLoginExpectation([$this->session, $person]);
        $this->createRouterExpectation($routerArgs, $url);

        $result = $this->controller->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
        $this->assertEquals($url, json_decode($result->getContent(), true)['url']);
    }

    /**
     * @test
     */
    public function noAuthenticationRequestIdInSession()
    {
        $organization = new OrganizationModel;
        $this->request->attributes->set('organization', $organization);
        $verificationPhrase = 'foo bar baz qux';
        $this->request->request->set('phrase', $verificationPhrase);

        $this->createSessionExpectation(['_signup_authentication_request_id'], null);

        $result = $this->controller->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(400, $result->getStatusCode());
    }

    private function createRouterExpectation($args, $result)
    {
        $this->router
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createLoginExpectation($args)
    {
        $this->login
            ->shouldReceive('populateSession')
            ->once()
            ->with(...$args);
    }

    private function createCreateFromApprovedEmailDomainExpectation($args, $result)
    {
        $this->createFromApprovedEmailDomain
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createVerifySignUpAuthenticationExpectation($args, $result)
    {
        $this->verifySignUpAuthentication
            ->shouldReceive('verifyWithPhrase')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSessionExpectation($args, $result)
    {
        $this->session
            ->shouldReceive('get')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
