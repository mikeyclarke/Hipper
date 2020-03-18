<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\SignUpFlow\Controller;

use Hipper\Api\SignUpFlow\Controller\VerifyIdentityController;
use Hipper\Person\CreationStrategy\CreateFoundingMember;
use Hipper\Person\PersonModel;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Hipper\SignUpAuthentication\VerifySignUpAuthentication;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class VerifyIdentityControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $createFoundingMember;
    private $verifySignUpAuthentication;
    private $verifyIdentityController;
    private $request;
    private $session;

    public function setUp(): void
    {
        $this->createFoundingMember = m::mock(CreateFoundingMember::class);
        $this->verifySignUpAuthentication = m::mock(VerifySignUpAuthentication::class);

        $this->verifyIdentityController = new VerifyIdentityController(
            $this->createFoundingMember,
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
        $phrase = 'foo bar baz qux';
        $requestBody = [
            'phrase' => $phrase,
        ];
        $this->request->request->add($requestBody);

        $authenticationRequestId = 'auth-req-uuid';
        $authenticationRequest = new SignUpAuthenticationModel;
        $personId = 'person-uuid';
        $person = PersonModel::createFromArray([
            'id' => $personId,
        ]);
        $this->createSessionGetExpectation(['_signup_authentication_request_id'], $authenticationRequestId);
        $this->createVerifySignUpAuthenticationExpectation(
            [$authenticationRequestId, $phrase],
            $authenticationRequest
        );
        $this->createCreateFoundingMemberExpectation([$authenticationRequest], $person);
        $this->createSessionSetExpectation(['_personId', $personId]);

        $result = $this->verifyIdentityController->postAction($this->request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals(200, $result->getStatusCode());
    }

    private function createSessionSetExpectation($args)
    {
        $this->session
            ->shouldReceive('set')
            ->once()
            ->with(...$args);
    }

    private function createCreateFoundingMemberExpectation($args, $result)
    {
        $this->createFoundingMember
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

    private function createSessionGetExpectation($args, $result)
    {
        $this->session
            ->shouldReceive('get')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
