<?php
declare(strict_types=1);

namespace Hipper\Tests\Invite;

use Doctrine\DBAL\Connection;
use Hipper\Invite\BulkInvitationProcessor;
use Hipper\Invite\InviteRepository;
use Hipper\Invite\Storage\InviteUpdater;
use Hipper\Organization\OrganizationRepository;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonRepository;
use Hipper\Security\TokenGenerator;
use Hipper\TransactionalEmail\BulkInvite;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BulkInvitationProcessorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $bulkInvite;
    private $connection;
    private $inviteRepository;
    private $inviteUpdater;
    private $organizationRepository;
    private $personRepository;
    private $tokenGenerator;
    private $processor;

    public function setUp(): void
    {
        $this->bulkInvite = m::mock(BulkInvite::class);
        $this->connection = m::mock(Connection::class);
        $this->inviteRepository = m::mock(InviteRepository::class);
        $this->inviteUpdater = m::mock(InviteUpdater::class);
        $this->organizationRepository = m::mock(OrganizationRepository::class);
        $this->personRepository = m::mock(PersonRepository::class);
        $this->tokenGenerator = m::mock(TokenGenerator::class);

        $this->processor = new BulkInvitationProcessor(
            $this->bulkInvite,
            $this->connection,
            $this->inviteRepository,
            $this->inviteUpdater,
            $this->organizationRepository,
            $this->personRepository,
            $this->tokenGenerator
        );
    }

    /**
     * @test
     */
    public function processInvitations()
    {
        $organizationId = 'orgamization-id';
        $personId = 'person-id';
        $domain = 'usehipper.test';
        $inviteIds = [
            'invite-id-one',
            'invite-id-two',
            'invite-id-three',
        ];

        $organizationArray = [
            'name' => 'Acme',
            'subdomain' => 'acme',
        ];
        $personArray = [
            'email_address' => 'mikey@usehipper.com',
            'name' => 'Mikey Clarke',
        ];
        $inviteRecords = [
            'invite-id-one' => ['email_address' => 'foo@example.com'],
            'invite-id-two' => ['email_address' => 'bar@example.com'],
            'invite-id-three' => ['email_address' => 'baz@example.com'],
        ];
        $tokens = [
            'token-one',
            'token-two',
            'token-three',
        ];
        $emailsToSend = [
            [
                'sender_email_address' => $personArray['email_address'],
                'sender_name' => $personArray['name'],
                'organization_name' => $organizationArray['name'],
                'recipient_email_address' => $inviteRecords['invite-id-one']['email_address'],
                'invite_link' => sprintf(
                    'https://%s.%s/join/by-invitation?i=%s&t=%s',
                    $organizationArray['subdomain'],
                    $domain,
                    $inviteIds[0],
                    $tokens[0],
                ),
            ],
            [
                'sender_email_address' => $personArray['email_address'],
                'sender_name' => $personArray['name'],
                'organization_name' => $organizationArray['name'],
                'recipient_email_address' => $inviteRecords['invite-id-two']['email_address'],
                'invite_link' => sprintf(
                    'https://%s.%s/join/by-invitation?i=%s&t=%s',
                    $organizationArray['subdomain'],
                    $domain,
                    $inviteIds[1],
                    $tokens[1],
                ),
            ],
            [
                'sender_email_address' => $personArray['email_address'],
                'sender_name' => $personArray['name'],
                'organization_name' => $organizationArray['name'],
                'recipient_email_address' => $inviteRecords['invite-id-three']['email_address'],
                'invite_link' => sprintf(
                    'https://%s.%s/join/by-invitation?i=%s&t=%s',
                    $organizationArray['subdomain'],
                    $domain,
                    $inviteIds[2],
                    $tokens[2],
                ),
            ],
        ];

        $this->createOrganizationRepositoryExpectation($organizationId, $organizationArray);
        $this->createPersonRepositoryExpectation($personId, $personArray);

        $this->createInviteRepositoryExpectation($inviteIds, $inviteRecords);
        $this->createConnectionBeginTransactionExpectation();

        $this->createTokenGeneratorExpectation($tokens[0]);
        $this->createInviteUpdaterExpectation($inviteIds[0], ['token' => $tokens[0]]);

        $this->createTokenGeneratorExpectation($tokens[1]);
        $this->createInviteUpdaterExpectation($inviteIds[1], ['token' => $tokens[1]]);

        $this->createTokenGeneratorExpectation($tokens[2]);
        $this->createInviteUpdaterExpectation($inviteIds[2], ['token' => $tokens[2]]);

        $this->createConnectionCommitExpectation();
        $this->createBulkInviteSendExpectation($emailsToSend);

        $this->createInviteUpdaterExpectation($inviteIds[0], ['sent' => true]);
        $this->createInviteUpdaterExpectation($inviteIds[1], ['sent' => true]);
        $this->createInviteUpdaterExpectation($inviteIds[2], ['sent' => true]);

        $this->processor->processInvitations($organizationId, $personId, $domain, $inviteIds);
    }

    private function createBulkInviteSendExpectation($data)
    {
        $this->bulkInvite
            ->shouldReceive('send')
            ->once()
            ->with($data);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createInviteUpdaterExpectation($id, $data)
    {
        $this->inviteUpdater
            ->shouldReceive('update')
            ->once()
            ->with($id, $data);
    }

    private function createTokenGeneratorExpectation($result)
    {
        $this->tokenGenerator
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

    private function createInviteRepositoryExpectation($ids, $result)
    {
        $this->inviteRepository
            ->shouldReceive('findWithIds')
            ->once()
            ->with($ids)
            ->andReturn($result);
    }

    private function createPersonRepositoryExpectation($id, $result)
    {
        $this->personRepository
            ->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($result);
    }

    private function createOrganizationRepositoryExpectation($id, $result)
    {
        $this->organizationRepository
            ->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($result);
    }
}
