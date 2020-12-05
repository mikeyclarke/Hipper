<?php

declare(strict_types=1);

namespace Hipper\Messenger\Message;

class InvitationsCreated implements AsyncMessageInterface
{
    private string $organizationId;
    private string $personId;
    private string $domain;
    private array $inviteIds;

    public function __construct(
        string $organizationId,
        string $personId,
        string $domain,
        array $inviteIds
    ) {
        $this->organizationId = $organizationId;
        $this->personId = $personId;
        $this->domain = $domain;
        $this->inviteIds = $inviteIds;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function getPersonId(): string
    {
        return $this->personId;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getInviteIds(): array
    {
        return $this->inviteIds;
    }
}
