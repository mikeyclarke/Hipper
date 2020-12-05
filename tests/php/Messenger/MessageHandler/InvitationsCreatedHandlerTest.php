<?php

declare(strict_types=1);

namespace Hipper\Tests\Messenger\MessageHandler;

use Hipper\Invite\BulkInvitationProcessor;
use Hipper\Messenger\MessageHandler\InvitationsCreatedHandler;
use Hipper\Messenger\Message\InvitationsCreated;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class InvitationsCreatedHandlerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $bulkInvitationProcessor;
    private $handler;

    public function setUp(): void
    {
        $this->bulkInvitationProcessor = m::mock(BulkInvitationProcessor::class);

        $this->handler = new InvitationsCreatedHandler(
            $this->bulkInvitationProcessor
        );
    }

    /**
     * @test
     */
    public function invoke()
    {
        $organizationId = 'org-uuid';
        $personId = 'person-uuid';
        $domain = 'usehipper.test';
        $inviteIds = ['invite-uuid-1', 'invite-uuid-2'];

        $message = new InvitationsCreated(
            $organizationId,
            $personId,
            $domain,
            $inviteIds
        );

        $this->createBulkInvitationProcessorExpectation([$organizationId, $personId, $domain, $inviteIds]);

        $this->handler->__invoke($message);
    }

    private function createBulkInvitationProcessorExpectation($args)
    {
        $this->bulkInvitationProcessor
            ->shouldReceive('processInvitations')
            ->once()
            ->with(...$args);
    }
}
