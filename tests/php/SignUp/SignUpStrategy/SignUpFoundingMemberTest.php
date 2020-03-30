<?php
declare(strict_types=1);

namespace Hipper\Tests\SignUp\SignUpStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Organization\Event\OrganizationCreatedEvent;
use Hipper\Organization\OrganizationCreator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Hipper\SignUp\Exception\AuthorizationRequestMissingOrganizationNameException;
use Hipper\SignUp\Exception\EmailAddressAlreadyInUseException;
use Hipper\SignUp\SignUpAuthorization;
use Hipper\SignUp\SignUpAuthorizationRequestModel;
use Hipper\SignUp\SignUpStrategy\SignUpFoundingMember;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignUpFoundingMemberTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $eventDispatcher;
    private $organizationCreator;
    private $personCreator;
    private $personRepository;
    private $signUpAuthorization;
    private $signUpFoundingMember;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->eventDispatcher = m::mock(EventDispatcherInterface::class);
        $this->organizationCreator = m::mock(OrganizationCreator::class);
        $this->personCreator = m::mock(PersonCreator::class);
        $this->personRepository = m::mock(PersonRepository::class);
        $this->signUpAuthorization = m::mock(SignUpAuthorization::class);

        $this->signUpFoundingMember = new SignUpFoundingMember(
            $this->connection,
            $this->eventDispatcher,
            $this->organizationCreator,
            $this->personCreator,
            $this->personRepository,
            $this->signUpAuthorization
        );
    }

    /**
     * @test
     */
    public function signUp()
    {
        $organizationName = 'Acme';
        $emailAddress = 'mikey@usehipper.com';
        $name = 'Mikey Clarke';
        $encodedPassword = 'encoded-password';

        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'organization_name' => $organizationName,
            'email_address' => $emailAddress,
            'encoded_password' => $encodedPassword,
            'name' => $name,
        ]);
        $input = ['phrase' => 'foo bar baz qux'];

        $organization = new OrganizationModel;
        $person = new PersonModel;

        $this->createSignUpAuthorizationExpectation([$authorizationRequest, $input]);
        $this->createPersonRepositoryExpectation([$emailAddress], false);
        $this->createConnectionBeginTransactionExpectation();
        $this->createOrganizationCreatorExpectation([$organizationName], $organization);
        $this->createPersonCreatorExpectation([$organization, $name, $emailAddress, $encodedPassword], $person);
        $this->createConnectionCommitExpectation();
        $this->createEventDispatcherExpectation(
            [m::type(OrganizationCreatedEvent::class), OrganizationCreatedEvent::NAME]
        );
        $this->createEventDispatcherExpectation(
            [m::type(PersonCreatedEvent::class), PersonCreatedEvent::NAME]
        );

        $result = $this->signUpFoundingMember->signUp($authorizationRequest, $input);
        $this->assertEquals($person, $result);
    }

    /**
     * @test
     */
    public function authorizationRequestMissingOrganizationName()
    {
        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'organization_name' => null,
        ]);
        $input = ['phrase' => 'foo bar baz qux'];

        $this->createSignUpAuthorizationExpectation([$authorizationRequest, $input]);

        $this->expectException(AuthorizationRequestMissingOrganizationNameException::class);

        $this->signUpFoundingMember->signUp($authorizationRequest, $input);
    }

    /**
     * @test
     */
    public function emailAddressAlreadyInUse()
    {
        $organizationName = 'Acme';
        $emailAddress = 'mikey@usehipper.com';

        $authorizationRequest = SignUpAuthorizationRequestModel::createFromArray([
            'organization_name' => $organizationName,
            'email_address' => $emailAddress,
        ]);
        $input = ['phrase' => 'foo bar baz qux'];

        $this->createSignUpAuthorizationExpectation([$authorizationRequest, $input]);
        $this->createPersonRepositoryExpectation([$emailAddress], true);

        $this->expectException(EmailAddressAlreadyInUseException::class);

        $this->signUpFoundingMember->signUp($authorizationRequest, $input);
    }

    private function createEventDispatcherExpectation($args)
    {
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(...$args);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createPersonCreatorExpectation($args, $result)
    {
        $this->personCreator
            ->shouldReceive('createWithEncodedPassword')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createOrganizationCreatorExpectation($args, $result)
    {
        $this->organizationCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createPersonRepositoryExpectation($args, $result)
    {
        $this->personRepository
            ->shouldReceive('existsWithEmailAddress')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSignUpAuthorizationExpectation($args)
    {
        $this->signUpAuthorization
            ->shouldReceive('authorize')
            ->once()
            ->with(...$args);
    }
}
