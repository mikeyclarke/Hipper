<?php
declare(strict_types=1);

namespace LithosTests\Invite;

use Doctrine\DBAL\Connection;
use Lithos\IdGenerator\IdGenerator;
use Lithos\Invite\BulkInvitationCreator;
use Lithos\Invite\BulkInvitationProcessor;
use Lithos\Invite\BulkInvitationValidator;
use Lithos\Invite\InviteInserter;
use Lithos\Person\PersonModel;
use Lithos\TransactionalEmail\BulkInvite;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BulkInvitationCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $bulkInvitationProcessor;
    private $validator;
    private $connection;
    private $idGenerator;
    private $inviteInserter;
    private $creator;

    public function setUp(): void
    {
        $this->bulkInvitationProcessor = m::mock(BulkInvitationProcessor::class);
        $this->validator = m::mock(BulkInvitationValidator::class);
        $this->connection = m::mock(Connection::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->inviteInserter = m::mock(InviteInserter::class);

        $this->creator = new BulkInvitationCreator(
            $this->bulkInvitationProcessor,
            $this->validator,
            $this->connection,
            $this->idGenerator,
            $this->inviteInserter
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $person = new PersonModel;
        $person->setId('person-id');
        $person->setOrganizationId('organization-id');
        $domain = 'tryhleo.test';
        $input = [
            'email_invites' => [
                'foo@example.com',
                'bar@example.com',
                'baz@example.com',
            ],
        ];
        $inviteIds = [
            'invite-one',
            'invite-two',
            'invite-three',
        ];

        $this->createBulkInvitationValidatorExpectation($input);
        $this->createConnectionBeginTransactionExpectation();

        $this->createInviteExpectations($person, $inviteIds[0], $input['email_invites'][0]);
        $this->createInviteExpectations($person, $inviteIds[1], $input['email_invites'][1]);
        $this->createInviteExpectations($person, $inviteIds[2], $input['email_invites'][2]);

        $this->createConnectionCommitExpectation();
        $this->createBulkInvitationProcessorExpectation(
            $person->getOrganizationId(),
            $person->getId(),
            $domain,
            $inviteIds
        );

        $this->creator->create($person, $domain, $input);
    }

    /**
     * @test
     */
    public function nullInvitations()
    {
        $person = new PersonModel;
        $domain = 'tryhleo.test';
        $input = [
            'email_invites' => null,
        ];

        $this->createBulkInvitationValidatorExpectation($input);

        $this->creator->create($person, $domain, $input);
    }

    /**
     * @test
     */
    public function emptyInvitations()
    {
        $person = new PersonModel;
        $domain = 'tryhleo.test';
        $input = [
            'email_invites' => [],
        ];

        $this->createBulkInvitationValidatorExpectation($input);

        $this->creator->create($person, $domain, $input);
    }

    private function createBulkInvitationProcessorExpectation($organizationId, $personId, $domain, $inviteIds)
    {
        $this->bulkInvitationProcessor
            ->shouldReceive('processInvitations')
            ->once()
            ->with($organizationId, $personId, $domain, $inviteIds);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createInviteExpectations(PersonModel $person, $inviteId, $emailAddress)
    {
        $this->createIdGeneratorExpectation($inviteId);
        $this->createInviteInserterExpectation(
            $inviteId,
            $emailAddress,
            $person->getId(),
            $person->getOrganizationId()
        );
    }

    private function createInviteInserterExpectation($id, $emailAddress, $personId, $organizationId)
    {
        $this->inviteInserter
            ->shouldReceive('insert')
            ->once()
            ->with(
                $id,
                $emailAddress,
                $personId,
                $organizationId,
                m::pattern('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/')
            );
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createBulkInvitationValidatorExpectation($input)
    {
        $this->validator
            ->shouldReceive('validate')
            ->once()
            ->with($input);
    }
}
