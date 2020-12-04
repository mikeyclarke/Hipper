<?php

declare(strict_types=1);

namespace Hipper\Messenger;

use Symfony\Component\Messenger\MessageBusInterface;

class MessageBus
{
    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus
    ) {
        $this->messageBus = $messageBus;
    }

    public function dispatch($message, array $stamps = []): void
    {
        $this->messageBus->dispatch($message, $stamps);
    }
}
