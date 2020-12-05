<?php

declare(strict_types=1);

namespace Hipper\Messenger\MessageHandler;

use Hipper\Invite\BulkInvitationProcessor;
use Hipper\Messenger\Message\InvitationsCreated;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class InvitationsCreatedHandler implements MessageHandlerInterface
{
    private BulkInvitationProcessor $bulkInvitationProcessor;

    public function __construct(
        BulkInvitationProcessor $bulkInvitationProcessor
    ) {
        $this->bulkInvitationProcessor = $bulkInvitationProcessor;
    }

    public function __invoke(InvitationsCreated $message): void
    {
        $this->bulkInvitationProcessor->processInvitations(
            $message->getOrganizationId(),
            $message->getPersonId(),
            $message->getDomain(),
            $message->getInviteIds()
        );
    }
}
