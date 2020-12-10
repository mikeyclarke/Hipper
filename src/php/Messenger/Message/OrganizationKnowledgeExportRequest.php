<?php

declare(strict_types=1);

namespace Hipper\Messenger\Message;

class OrganizationKnowledgeExportRequest implements LowPriorityAsyncMessageInterface
{
    private string $organizationId;
    private array $recipientAddresses;

    public function __construct(
        string $organizationId,
        array $recipientAddresses
    ) {
        $this->organizationId = $organizationId;
        $this->recipientAddresses = $recipientAddresses;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function getRecipientAddresses(): array
    {
        return $this->recipientAddresses;
    }
}
