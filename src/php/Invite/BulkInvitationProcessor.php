<?php
declare(strict_types=1);

namespace Lithos\Invite;

use Doctrine\DBAL\Connection;
use Lithos\Organization\Organization;
use Lithos\Organization\OrganizationModel;
use Lithos\Person\PersonModel;
use Lithos\Person\PersonModelMapper;
use Lithos\Person\PersonRepository;
use Lithos\Security\TokenGenerator;
use Lithos\TransactionalEmail\BulkInvite;

class BulkInvitationProcessor
{
    private $bulkInvite;
    private $connection;
    private $inviteRepository;
    private $inviteUpdater;
    private $organization;
    private $personModelMapper;
    private $personRepository;
    private $tokenGenerator;

    public function __construct(
        BulkInvite $bulkInvite,
        Connection $connection,
        InviteRepository $inviteRepository,
        InviteUpdater $inviteUpdater,
        Organization $organization,
        PersonModelMapper $personModelMapper,
        PersonRepository $personRepository,
        TokenGenerator $tokenGenerator
    ) {
        $this->bulkInvite = $bulkInvite;
        $this->connection = $connection;
        $this->inviteRepository = $inviteRepository;
        $this->inviteUpdater = $inviteUpdater;
        $this->organization = $organization;
        $this->personModelMapper = $personModelMapper;
        $this->personRepository = $personRepository;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function processInvitations(
        string $organizationId,
        string $personId,
        string $domain,
        array $inviteIds
    ): void {
        $organization = $this->organization->get($organizationId);
        if (null === $organization) {
            throw new \UnexpectedValueException('Organization does not exist');
        }
        $person = $this->personRepository->findById($personId);
        if (null === $person) {
            throw new \UnexpectedValueException('Person does not exist');
        }
        $person = $this->personModelMapper->createFromArray($person);

        $inviteRecords = $this->inviteRepository->findWithIds($inviteIds);

        $this->connection->beginTransaction();
        try {
            $emailsToSend = $this->prepareInvites($organization, $person, $domain, $inviteIds, $inviteRecords);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
        }

        $this->bulkInvite->send($emailsToSend);
        $this->markAsSent($inviteIds);
    }

    private function prepareInvites(
        OrganizationModel $organization,
        PersonModel $person,
        string $domain,
        array $inviteIds,
        array $inviteRecords
    ): array {
        $emailsToSend = [];
        foreach ($inviteIds as $id) {
            if (!isset($inviteRecords[$id])) {
                throw new \UnexpectedValueException('Invite record does not exist');
            }
            $inviteRecord = $inviteRecords[$id];

            $token = $this->tokenGenerator->generate();
            $this->inviteUpdater->update($id, ['token' => $token]);

            $emailsToSend[] = [
                'sender_email_address' => $person->getEmailAddress(),
                'sender_name' => $person->getName(),
                'organization_name' => $organization->getName(),
                'recipient_email_address' => $inviteRecord['email_address'],
                'invite_link' => $this->composeInviteLink(
                    $domain,
                    $organization->getSubdomain(),
                    $id,
                    $token
                ),
            ];
        }
        return $emailsToSend;
    }

    private function markAsSent(array $inviteIds): void
    {
        foreach ($inviteIds as $id) {
            $this->inviteUpdater->update($id, ['sent' => true]);
        }
    }

    private function composeInviteLink(
        string $domain,
        string $organizationSubdomain,
        string $id,
        string $token
    ): string {
        return sprintf(
            'https://%s.%s/join/by-invitation?i=%s&t=%s',
            $organizationSubdomain,
            $domain,
            $id,
            $token
        );
    }
}