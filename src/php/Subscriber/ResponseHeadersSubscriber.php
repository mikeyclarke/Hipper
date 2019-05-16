<?php
declare(strict_types=1);

namespace Lithos\Subscriber;

use Lithos\Security\ContentSecurityPolicyBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseHeadersSubscriber implements EventSubscriberInterface
{
    private $cspBuilder;
    private $cspEnabled;
    private $hstsEnabled;
    private $hstsMaxAge;

    public function __construct(
        ContentSecurityPolicyBuilder $cspBuilder,
        bool $cspEnabled,
        bool $hstsEnabled,
        int $hstsMaxAge
    ) {
        $this->cspBuilder = $cspBuilder;
        $this->cspEnabled = $cspEnabled;
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

        if ($this->cspEnabled) {
            $headers['Content-Security-Policy'] = $this->cspBuilder->build();
        }

        if ($this->hstsEnabled) {
            $headers['Strict-Transport-Security'] = sprintf(
                'max-age=%d; includeSubdomains; preload',
                $this->hstsMaxAge
            );
        }

        $response->headers->add($headers);
    }
}
