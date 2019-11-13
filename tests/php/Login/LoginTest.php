<?php
declare(strict_types=1);

namespace Hipper\Tests\Login;

use Hipper\Login\Exception\InvalidCredentialsException;
use Hipper\Login\Login;
use Hipper\Login\LoginValidator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonPasswordEncoder;
use Hipper\Person\PersonRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LoginTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $loginValidator;
    private $passwordEncoder;
    private $personRepository;
    private $login;

    public function setUp(): void
    {
        $this->loginValidator = m::mock(LoginValidator::class);
        $this->passwordEncoder = m::mock(PersonPasswordEncoder::class);
        $this->personRepository = m::mock(PersonRepository::class);

        $this->login = new Login(
            $this->loginValidator,
            $this->passwordEncoder,
            $this->personRepository
        );
    }

    /**
     * @test
     */
    public function login()
    {
        $organization = new OrganizationModel;
        $organization->setId('org-id');
        $parameters = [
            'email_address' => 'hello@example.com',
            'password' => 'gb6uyjn9hgg2h0',
        ];
        $session = m::mock(SessionInterface::class);

        $person = [
            'id' => 'person-id',
            'password' => 'gb6uyjn9hgg2h0',
        ];

        $this->createPersonRepositoryExpectation([$parameters['email_address'], $organization->getId()], $person);
        $this->createLoginValidatorExpectation([$parameters]);
        $this->createPasswordEncoderExpectation([$person['password'], $parameters['password']], true);

        $session
            ->shouldReceive('set')
            ->once()
            ->with('_personId', $person['id']);

        $this->login->login($organization, $parameters, $session);
    }

    /**
     * @test
     */
    public function noPersonWithEmailAddress()
    {
        $organization = new OrganizationModel;
        $organization->setId('org-id');
        $parameters = [
            'email_address' => 'hello@example.com',
            'password' => 'gb6uyjn9hgg2h0',
        ];
        $session = m::mock(SessionInterface::class);

        $person = null;

        $this->expectException(InvalidCredentialsException::class);

        $this->createPersonRepositoryExpectation([$parameters['email_address'], $organization->getId()], $person);
        $this->createLoginValidatorExpectation([$parameters]);

        $this->login->login($organization, $parameters, $session);
    }

    /**
     * @test
     */
    public function invalidPassword()
    {
        $organization = new OrganizationModel;
        $organization->setId('org-id');
        $parameters = [
            'email_address' => 'hello@example.com',
            'password' => 'gb6uyjn9hgg2h0',
        ];
        $session = m::mock(SessionInterface::class);

        $person = [
            'id' => 'person-id',
            'password' => 'not-the-same',
        ];

        $this->expectException(InvalidCredentialsException::class);

        $this->createPersonRepositoryExpectation([$parameters['email_address'], $organization->getId()], $person);
        $this->createLoginValidatorExpectation([$parameters]);
        $this->createPasswordEncoderExpectation([$person['password'], $parameters['password']], false);

        $this->login->login($organization, $parameters, $session);
    }

    private function createPasswordEncoderExpectation($args, $result)
    {
        $this->passwordEncoder
            ->shouldReceive('isPasswordValid')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createLoginValidatorExpectation($args)
    {
        $this->loginValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }

    private function createPersonRepositoryExpectation($args, $result)
    {
        $this->personRepository
            ->shouldReceive('findOneByEmailAddress')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
