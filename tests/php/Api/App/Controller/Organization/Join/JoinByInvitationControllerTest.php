<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\App\Controller\Organization\Join;

use Hipper\Api\App\Controller\Organization\Join\JoinByInvitationController;
use Hipper\Login\Login;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use Hipper\SignUp\SignUpStrategy\SignUpFromInvitation;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class JoinByInvitationControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $login;
    private $signUpFromInvitation;
    private $router;
    private $controller;
    private $request;
    private $session;

    public function setUp(): void
    {
        $this->login = m::mock(Login::class);
        $this->signUpFromInvitation = m::mock(SignUpFromInvitation::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->controller = new JoinByInvitationController(
            $this->login,
            $this->signUpFromInvitation,
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
        $requestBody = ['invite_id' => 'invite-uuid'];
        $this->request->request->add($requestBody);

        $organizationSubdomain = 'acme';
        $organization = OrganizationModel::createFromArray([
            'subdomain' => $organizationSubdomain,
        ]);
        $this->request->attributes->set('organization', $organization);

        $person = new PersonModel;
        $routeName = 'front_end.app.organization.home';
        $url = '/';

        $this->createSignUpFromInvitationExpectation([$organization, $requestBody], $person);
        $this->createLoginExpectation([$this->session, $person]);
        $this->createRouterExpectation([$routeName, ['subdomain' => $organizationSubdomain]], $url);

        $result = $this->controller->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(201, $result->getStatusCode());
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

    private function createSignUpFromInvitationExpectation($args, $result)
    {
        $this->signUpFromInvitation
            ->shouldReceive('signUp')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
