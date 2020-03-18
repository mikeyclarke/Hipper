<?php
declare(strict_types=1);

namespace Hipper\Tests\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Organization\Event\OrganizationCreatedEvent;
use Hipper\Organization\OrganizationCreator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\CreationStrategy\CreateFoundingMember;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonCreationValidator;
use Hipper\SignUpAuthentication\SignUpAuthenticationModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateFoundingMemberTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $eventDispatcher;
    private $organizationCreator;
    private $personCreationValidator;
    private $personCreator;
    private $requestEmailAddressVerification;
    private $createFoundingMember;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->eventDispatcher = m::mock(EventDispatcherInterface::class);
        $this->organizationCreator = m::mock(OrganizationCreator::class);
        $this->personCreationValidator = m::mock(PersonCreationValidator::class);
        $this->personCreator = m::mock(PersonCreator::class);
        $this->requestEmailAddressVerification = m::mock(RequestEmailAddressVerification::class);

        $this->createFoundingMember = new CreateFoundingMember(
            $this->connection,
            $this->eventDispatcher,
            $this->organizationCreator,
            $this->personCreationValidator,
            $this->personCreator,
            $this->requestEmailAddressVerification
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $name = 'Mikey Clarke';
        $emailAddress = 'mikey@usehipper.com';
        $encodedPassword = 'encoded-password';
        $authenticationRequest = SignUpAuthenticationModel::createFromArray([
            'id' => 'auth-req-uuid',
            'email_address' => $emailAddress,
            'encoded_password' => $encodedPassword,
            'name' => $name,
            'verification_phrase' => 'foo bar baz qux',
        ]);

        $validationProperties = [
            'email_address' => $emailAddress,
            'name' => $name,
        ];
        $organization = new OrganizationModel;
        $person = new PersonModel;

        $this->createPersonCreationValidatorExpectation([$validationProperties]);
        $this->createConnectionBeginTransactionExpectation();
        $this->createOrganizationCreatorExpectation($organization);
        $this->createPersonCreatorExpectation(
            [$organization, $name, $emailAddress, $encodedPassword],
            $person
        );
        $this->createConnectionCommitExpectation();
        $this->createEventDispatcherExpectation(
            [m::type(OrganizationCreatedEvent::class), OrganizationCreatedEvent::NAME]
        );
        $this->createEventDispatcherExpectation(
            [m::type(PersonCreatedEvent::class), PersonCreatedEvent::NAME]
        );

        $result = $this->createFoundingMember->create($authenticationRequest);
        $this->assertEquals($person, $result);
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

    private function createOrganizationCreatorExpectation($result)
    {
        $this->organizationCreator
            ->shouldReceive('create')
            ->once()
            ->andReturn($result);
    }

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createPersonCreationValidatorExpectation($args)
    {
        $this->personCreationValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
