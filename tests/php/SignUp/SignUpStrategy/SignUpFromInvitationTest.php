<?php
declare(strict_types=1);

namespace Hipper\Tests\SignUp\SignUpStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Invite\InviteModel;
use Hipper\Invite\InviteRepository;
use Hipper\Invite\Storage\InviteDeleter;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\Event\PersonCreatedEvent;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use Hipper\Person\PersonRepository;
use Hipper\SignUp\Exception\EmailAddressAlreadyInUseException;
use Hipper\SignUp\Exception\InviteExpiredException;
use Hipper\SignUp\Exception\InviteNotFoundException;
use Hipper\SignUp\SignUpStrategy\SignUpFromInvitation;
use Hipper\SignUp\SignUpValidation\InvitationSignUpValidator;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignUpFromInvitationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $eventDispatcher;
    private $validator;
    private $inviteDeleter;
    private $inviteRepository;
    private $personCreator;
    private $personRepository;
    private $signUpFromInvitation;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->eventDispatcher = m::mock(EventDispatcherInterface::class);
        $this->validator = m::mock(InvitationSignUpValidator::class);
        $this->inviteDeleter = m::mock(InviteDeleter::class);
        $this->inviteRepository = m::mock(InviteRepository::class);
        $this->personCreator = m::mock(PersonCreator::class);
        $this->personRepository = m::mock(PersonRepository::class);

        $this->signUpFromInvitation = new SignUpFromInvitation(
            $this->connection,
            $this->eventDispatcher,
            $this->validator,
            $this->inviteDeleter,
            $this->inviteRepository,
            $this->personCreator,
            $this->personRepository
        );
    }

    /**
     * @test
     */
    public function signUp()
    {
        $organizationId = 'org-uuid';
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
        ]);

        $inviteId = 'invite-uuid';
        $inviteToken = 'invite-token';
        $name = 'Mikey Clarke';
        $password = 'p455w0rd';
        $input = [
            'invite_id' => $inviteId,
            'invite_token' => $inviteToken,
            'name' => $name,
            'password' => $password,
        ];

        $emailAddress = 'mikey@usehipper.com';
        $expiryDate = new \DateTime('tomorrow');
        $inviteResult = [
            'id' => $inviteId,
            'email_address' => $emailAddress,
            'expires' => $expiryDate->format('Y-m-d H:i:s'),
        ];
        $person = new PersonModel;

        $this->createValidatorExpectation([$input]);
        $this->createInviteRepositoryExpectation([$inviteId, $organizationId, $inviteToken], $inviteResult);
        $this->createPersonRepositoryExpectation([$emailAddress], false);
        $this->createConnectionBeginTransactionExpectation();
        $this->createPersonCreatorExpectation([$organization, $name, $emailAddress, $password], $person);
        $this->createInviteDeleterExpectation([$inviteId]);
        $this->createConnectionCommitExpectation();
        $this->createEventDispatcherExpectation([m::type(PersonCreatedEvent::class), PersonCreatedEvent::NAME]);

        $result = $this->signUpFromInvitation->signUp($organization, $input);
        $this->assertEquals($person, $result);
    }

    /**
     * @test
     */
    public function inviteNotFound()
    {
        $organizationId = 'org-uuid';
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
        ]);

        $inviteId = 'invite-uuid';
        $inviteToken = 'invite-token';
        $name = 'Mikey Clarke';
        $password = 'p455w0rd';
        $input = [
            'invite_id' => $inviteId,
            'invite_token' => $inviteToken,
            'name' => $name,
            'password' => $password,
        ];

        $this->createValidatorExpectation([$input]);
        $this->createInviteRepositoryExpectation([$inviteId, $organizationId, $inviteToken], null);

        $this->expectException(InviteNotFoundException::class);

        $this->signUpFromInvitation->signUp($organization, $input);
    }

    /**
     * @test
     */
    public function inviteExpired()
    {
        $organizationId = 'org-uuid';
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
        ]);

        $inviteId = 'invite-uuid';
        $inviteToken = 'invite-token';
        $name = 'Mikey Clarke';
        $password = 'p455w0rd';
        $input = [
            'invite_id' => $inviteId,
            'invite_token' => $inviteToken,
            'name' => $name,
            'password' => $password,
        ];

        $emailAddress = 'mikey@usehipper.com';
        $expiryDate = new \DateTime('yesterday');
        $inviteResult = [
            'id' => $inviteId,
            'email_address' => $emailAddress,
            'expires' => $expiryDate->format('Y-m-d H:i:s'),
        ];

        $this->createValidatorExpectation([$input]);
        $this->createInviteRepositoryExpectation([$inviteId, $organizationId, $inviteToken], $inviteResult);

        $this->expectException(InviteExpiredException::class);

        $this->signUpFromInvitation->signUp($organization, $input);
    }

    /**
     * @test
     */
    public function emailAddressAlreadyInUse()
    {
        $organizationId = 'org-uuid';
        $organization = OrganizationModel::createFromArray([
            'id' => $organizationId,
        ]);

        $inviteId = 'invite-uuid';
        $inviteToken = 'invite-token';
        $name = 'Mikey Clarke';
        $password = 'p455w0rd';
        $input = [
            'invite_id' => $inviteId,
            'invite_token' => $inviteToken,
            'name' => $name,
            'password' => $password,
        ];

        $emailAddress = 'mikey@usehipper.com';
        $expiryDate = new \DateTime('tomorrow');
        $inviteResult = [
            'id' => $inviteId,
            'email_address' => $emailAddress,
            'expires' => $expiryDate->format('Y-m-d H:i:s'),
        ];

        $this->createValidatorExpectation([$input]);
        $this->createInviteRepositoryExpectation([$inviteId, $organizationId, $inviteToken], $inviteResult);
        $this->createPersonRepositoryExpectation([$emailAddress], true);

        $this->expectException(EmailAddressAlreadyInUseException::class);

        $this->signUpFromInvitation->signUp($organization, $input);
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

    private function createInviteDeleterExpectation($args)
    {
        $this->inviteDeleter
            ->shouldReceive('delete')
            ->once()
            ->with(...$args);
    }

    private function createPersonCreatorExpectation($args, $result)
    {
        $this->personCreator
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

    private function createInviteRepositoryExpectation($args, $result)
    {
        $this->inviteRepository
            ->shouldReceive('find')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createValidatorExpectation($args)
    {
        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
