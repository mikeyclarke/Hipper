<?php
declare(strict_types=1);

namespace Hipper\Subscriber;

use Hipper\Security\ContentSecurityPolicyBuilder;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseHeadersSubscriber implements EventSubscriberInterface
{
    private $cspBuilder;
    private $packages;
    private $cspEnabled;
    private $hstsEnabled;
    private $hstsMaxAge;
    private $resourceHintsEnabled;

    public function __construct(
        ContentSecurityPolicyBuilder $cspBuilder,
        Packages $packages,
        bool $cspEnabled,
        bool $hstsEnabled,
        int $hstsMaxAge,
        bool $resourceHintsEnabled
    ) {
        $this->cspBuilder = $cspBuilder;
        $this->packages = $packages;
        $this->cspEnabled = $cspEnabled;
        $this->hstsEnabled = $hstsEnabled;
        $this->hstsMaxAge = $hstsMaxAge;
        $this->resourceHintsEnabled = $resourceHintsEnabled;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 0],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
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

        if ($this->shouldSendResourceHints($response)) {
            $this->addResourceHint($response, 'app.css', 'style');
            $this->addResourceHint($response, 'app.js', 'script');
        }
    }

    private function shouldSendResourceHints(Response $response): bool
    {
        if (!$this->resourceHintsEnabled) {
            return false;
        }

        if ($response instanceof RedirectResponse || $response instanceof JsonResponse) {
            return false;
        }

        return true;
    }

    private function addResourceHint(Response $response, string $path, string $type): void
    {
        $value = sprintf('<%s>; rel=preload; as=%s; crossorigin', $this->packages->getUrl($path), $type);
        $response->headers->set('Link', $value, false);
    }
}
