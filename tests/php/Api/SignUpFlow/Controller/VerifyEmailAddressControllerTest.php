<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\SignUpFlow\Controller;

use Hipper\Api\SignUpFlow\Controller\VerifyEmailAddressController;
use Hipper\Person\PersonModel;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\SignUp\SignUpAuthorizationRequestRepository;
use Hipper\SignUp\SignUpStrategy\SignUpFoundingMember;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class VerifyEmailAddressControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $signUpAuthorizationRequestRepository;
    private $signUpFoundingMember;
    private $router;
    private $verifyEmailAddressController;
    private $request;
    private $session;

    public function setUp(): void
    {
        $this->signUpAuthorizationRequestRepository = m::mock(SignUpAuthorizationRequestRepository::class);
        $this->signUpFoundingMember = m::mock(SignUpFoundingMember::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->verifyEmailAddressController = new VerifyEmailAddressController(
            $this->signUpAuthorizationRequestRepository,
            $this->signUpFoundingMember,
            $this->router,
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
        $phrase = 'foo bar baz qux';
        $requestBody = [
            'phrase' => $phrase,
        ];
        $this->request->request->add($requestBody);

        $authorizationRequestId = 'auth-req-uuid';
        $authorizationRequestArray = [
            'organization_name' => 'Acme',
            'name' => 'Mikey Clarke',
            'email_address' => 'mikey@usehipper.com',
            'encoded_password' => 'encoded-password',
            'verification_phrase' => $phrase,
        ];
        $personId = 'person-uuid';
        $person = PersonModel::createFromArray([
            'id' => $personId,
        ]);
        $routeName = 'front_end.sign_up_flow.choose_organization_url';
        $url = '/sign-up/choose-organization-url';

        $this->createSessionGetExpectation(['_signup_authorization_request_id'], $authorizationRequestId);
        $this->createSignUpAuthorizationRequestRepositoryExpectation(
            [$authorizationRequestId],
            $authorizationRequestArray
        );
        $this->createSignUpFoundingMemberExpectation(
            [m::type(SignUpAuthorizationRequestModel::class), $requestBody],
            $person
        );
        $this->createSessionSetExpectation(['_personId', $personId]);
        $this->createRouterExpectation([$routeName], $url);

        $result = $this->verifyEmailAddressController->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
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

    private function createSessionSetExpectation($args)
    {
        $this->session
            ->shouldReceive('set')
            ->once()
            ->with(...$args);
    }

    private function createSignUpFoundingMemberExpectation($args, $result)
    {
        $this->signUpFoundingMember
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

    private function createSessionGetExpectation($args, $result)
    {
        $this->session
            ->shouldReceive('get')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
