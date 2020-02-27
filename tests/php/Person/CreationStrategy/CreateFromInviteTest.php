<?php
declare(strict_types=1);

namespace Hipper\Tests\Person\CreationStrategy;

use Doctrine\DBAL\Connection;
use Hipper\Invite\InviteRepository;
use Hipper\Invite\Storage\InviteDeleter;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\CreationStrategy\CreateFromInvite;
use Hipper\Person\Exception\InviteDoesNotBelongToOrganizationException;
use Hipper\Person\Exception\InviteNotFoundException;
use Hipper\Person\PersonCreationValidator;
use Hipper\Person\PersonCreator;
use Hipper\Person\PersonModel;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class CreateFromInviteTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $inviteDeleter;
    private $inviteRepository;
    private $personCreationValidator;
    private $personCreator;
    private $createFromInvite;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->inviteDeleter = m::mock(InviteDeleter::class);
        $this->inviteRepository = m::mock(InviteRepository::class);
        $this->personCreationValidator = m::mock(PersonCreationValidator::class);
        $this->personCreator = m::mock(PersonCreator::class);

        $this->createFromInvite = new CreateFromInvite(
            $this->connection,
            $this->inviteDeleter,
            $this->inviteRepository,
            $this->personCreationValidator,
            $this->personCreator
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $organization = new OrganizationModel;
        $organization->setId('org-id');
        $input = [
            'name' => 'Mikey Clarke',
            'password' => 'eufadkbjhs',
            'terms_agreed' => true,
            'invite_id' => 'invite-id',
            'invite_token' => 'token',
        ];

        $invite = [
            'id' => 'invite-id',
            'email_address' => 'mikey@usehipper.com',
            'organization_id' => 'org-id',
        ];
        $person = new PersonModel;
        $encodedPassword = 'encoded-password';

        $this->createPersonCreationValidatorExpectation($input);
        $this->createInviteRepositoryExpectation(
            $organization->getId(),
            $input['invite_id'],
            $input['invite_token'],
            $invite
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createPersonCreatorExpectation(
            $organization,
            $input['name'],
            $invite['email_address'],
            $input['password'],
            [$person, $encodedPassword]
        );
        $this->createInviteDeleterExpectation($invite['id']);
        $this->createConnectionCommitExpectation();

        $result = $this->createFromInvite->create($organization, $input);
        $this->assertEquals([$person, $encodedPassword], $result);
    }

    /**
     * @test
     */
    public function inviteDoesNotExist()
    {
        $this->expectException(InviteNotFoundException::class);

        $organization = new OrganizationModel;
        $organization->setId('org-id');
        $input = [
            'name' => 'Mikey Clarke',
            'password' => 'eufadkbjhs',
            'terms_agreed' => true,
            'invite_id' => 'invite_id',
            'invite_token' => 'invite_token',
        ];

        $this->createPersonCreationValidatorExpectation($input);
        $this->createInviteRepositoryExpectation(
            $organization->getId(),
            $input['invite_id'],
            $input['invite_token'],
            null
        );

        $this->createFromInvite->create($organization, $input);
    }

    private function createInviteDeleterExpectation($id)
    {
        $this->inviteDeleter
            ->shouldReceive('delete')
            ->once()
            ->with($id);
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
            ->with($organization, $name, $emailAddress, $password, true)
            ->andReturn($result);
    }

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createInviteRepositoryExpectation($organizationId, $inviteId, $token, $result)
    {
        $this->inviteRepository
            ->shouldReceive('find')
            ->once()
            ->with($inviteId, $organizationId, $token)
            ->andReturn($result);
    }

    private function createPersonCreationValidatorExpectation($input)
    {
        $this->personCreationValidator
            ->shouldReceive('validate')
            ->once()
            ->with($input, 'invite');
    }
}
