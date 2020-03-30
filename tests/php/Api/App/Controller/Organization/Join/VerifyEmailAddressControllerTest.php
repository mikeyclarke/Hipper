<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\App\Controller\Organization\Join;

use Hipper\Api\App\Controller\Organization\Join\VerifyEmailAddressController;
use Hipper\Login\Login;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\SignUp\SignUpAuthorizationRequestRepository;
use Hipper\SignUp\SignUpStrategy\SignUpFromApprovedEmailDomain;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VerifyEmailAddressControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $login;
    private $signUpAuthorizationRequestRepository;
    private $signUpFromApprovedEmailDomain;
    private $router;
    private $controller;
    private $request;
    private $session;

    public function setUp(): void
    {
        $this->login = m::mock(Login::class);
        $this->signUpAuthorizationRequestRepository = m::mock(SignUpAuthorizationRequestRepository::class);
        $this->signUpFromApprovedEmailDomain = m::mock(SignUpFromApprovedEmailDomain::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->controller = new VerifyEmailAddressController(
            $this->login,
            $this->signUpAuthorizationRequestRepository,
            $this->signUpFromApprovedEmailDomain,
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
        $organizationId = 'org-uuid';
        $organizationSubdomain = 'acme';
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
            'subdomain' => $organizationSubdomain,
        ]);
        $this->request->attributes->set('organization', $organization);
        $verificationPhrase = 'foo bar baz qux';
        $requestBody = [
            'phrase' => $verificationPhrase,
        ];
        $this->request->request->add($requestBody);

        $authorizationRequestId = 'auth-req-uuid';
        $authorizationRequestArray = [
            'organization_id' => $organizationId,
            'name' => 'Mikey Clarke',
            'email_address' => 'mikey@usehipper.com',
            'encoded_password' => 'encoded-password',
            'verification_phrase' => $verificationPhrase,
        ];
        $authorizationRequestId = 'signup-auth-uuid';
        $person = new PersonModel;
        $routerArgs = ['front_end.app.organization.home', ['subdomain' => $organizationSubdomain]];
        $url = '/';

        $this->createSessionExpectation(['_signup_authorization_request_id'], $authorizationRequestId);
        $this->createSignUpAuthorizationRequestRepositoryExpectation(
            [$authorizationRequestId],
            $authorizationRequestArray
        );
        $this->createSignUpFromApprovedEmailDomainExpectation(
            [m::type(SignUpAuthorizationRequestModel::class), $organization, $requestBody],
            $person
        );
        $this->createLoginExpectation([$this->session, $person]);
        $this->createRouterExpectation($routerArgs, $url);

        $result = $this->controller->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
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

    private function createLoginExpectation($args)
    {
        $this->login
            ->shouldReceive('populateSession')
            ->once()
            ->with(...$args);
    }

    private function createSignUpFromApprovedEmailDomainExpectation($args, $result)
    {
        $this->signUpFromApprovedEmailDomain
            ->shouldReceive('signUp')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSignUpAuthorizationRequestRepositoryExpectation($args, $result)
    {
        $this->signUpAuthorizationRequestRepository
            ->shouldReceive('findById')
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
