<?php
declare(strict_types=1);

namespace Hipper\Subscriber;

use Hipper\Http\HttpUserAgentProfiler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class UserAgentProfileSubscriber implements EventSubscriberInterface
{
    const SESSION_ATTRIBUTE = 'user_agent_profile';

    private $httpUserAgentProfiler;

    public function __construct(
        HttpUserAgentProfiler $httpUserAgentProfiler
    ) {
        $this->httpUserAgentProfiler = $httpUserAgentProfiler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -5]
        ];
    }

    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();

        if ($session->has(self::SESSION_ATTRIBUTE)) {
            return;
        }

        $userAgent = $request->headers->get('User-Agent');
        $userAgentProfile = $this->httpUserAgentProfiler->getProfile($userAgent);
        $session->set(self::SESSION_ATTRIBUTE, $userAgentProfile);
    }
}
