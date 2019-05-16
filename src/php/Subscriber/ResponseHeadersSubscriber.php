<?php
declare(strict_types=1);

namespace Lithos\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseHeadersSubscriber implements EventSubscriberInterface
{
    private $hstsEnabled;
    private $hstsMaxAge;

    public function __construct(
        bool $hstsEnabled,
        int $hstsMaxAge
    ) {
        $this->hstsEnabled = $hstsEnabled;
        $this->hstsMaxAge = $hstsMaxAge;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelResponse(FilterResponseEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $response = $event->getResponse();

        $headers = [
            'Cache-Control' => 'no-store, no-cache',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
        ];
        if ($this->hstsEnabled) {
            $headers['Strict-Transport-Security'] = sprintf(
                'max-age=%d; includeSubdomains; preload',
                $this->hstsMaxAge
            );
        }

        $response->headers->add($headers);
    }
}
