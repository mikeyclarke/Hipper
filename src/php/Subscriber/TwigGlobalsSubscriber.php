<?php
declare(strict_types=1);

namespace Hipper\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig_Environment;

class TwigGlobalsSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $assetDomain;

    public function __construct(
        Twig_Environment $twig,
        string $assetDomain
    ) {
        $this->twig = $twig;
        $this->assetDomain = $assetDomain;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -50],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();

        if ($request->headers->has('X-Requested-With') && $request->headers->get('X-Requested-With') === 'Fetch') {
            return;
        }

        if ($request->attributes->get('isOrganizationContext') === true) {
            $organization = $request->attributes->get('organization');
            $this->twig->addGlobal('organization', $organization);
        }

        if ($request->attributes->has('person')) {
            $person = $request->attributes->get('person');
            $this->twig->addGlobal('person', $person);
        }

        $session = $request->getSession();
        if ($session->has('csrf_token')) {
            $this->twig->addGlobal('csrf_token', $session->get('csrf_token'));
        }

        if ($session->has('user_agent_profile')) {
            $this->twig->addGlobal('user_agent_profile', $session->get('user_agent_profile'));
        }

        $this->twig->addGlobal('asset_domain', $this->assetDomain);
    }
}
