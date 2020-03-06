<?php
declare(strict_types=1);

namespace Hipper\Tests\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\EmailAddressVerification\RequestEmailAddressVerification;
use Hipper\Organization\Event\OrganizationCreatedEvent;
use Hipper\Organization\OrganizationCreator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\CreationStrategy\CreateFoundingMember;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonCreationValidator;
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
        $input = [
            'name' => 'Mikey Clarke',
            'email_address' => 'mikey@usehipper.com',
            'password' => '32gyewg7sy',
        ];
        $organization = new OrganizationModel;

        $person = new PersonModel;
        $encodedPassword = 'encoded-password';

        $this->createPersonCreationValidatorExpectation($input);
        $this->createConnectionBeginTransactionExpectation();
        $this->createOrganizationCreatorExpectation($organization);
        $this->createPersonCreatorExpectation(
            $organization,
            $input['name'],
            $input['email_address'],
            $input['password'],
            [$person, $encodedPassword]
        );
        $this->createConnectionCommitExpectation();
        $this->createRequestEmailAddressVerificationExpectation($person);
        $this->createEventDispatcherExpectation(
            [m::type(OrganizationCreatedEvent::class), OrganizationCreatedEvent::NAME]
        );
        $this->createEventDispatcherExpectation([m::type(PersonCreatedEvent::class), PersonCreatedEvent::NAME]);

        $result = $this->createFoundingMember->create($input);
        $this->assertEquals([$person, $encodedPassword], $result);
    }

    private function createEventDispatcherExpectation($args)
    {
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->once()
            ->with(...$args);
    }

    private function createRequestEmailAddressVerificationExpectation($person)
    {
        $this->requestEmailAddressVerification
            ->shouldReceive('sendVerificationRequest')
            ->once()
            ->with($person);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createPersonCreatorExpectation($organization, $name, $emailAddress, $password, $result)
    {
        $this->personCreator
            ->shouldReceive('create')
            ->once()
            ->with($organization, $name, $emailAddress, $password)
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

    private function createPersonCreationValidatorExpectation($input)
    {
        $this->personCreationValidator
            ->shouldReceive('validate')
            ->once()
            ->with($input, 'founding_member');
    }
}
