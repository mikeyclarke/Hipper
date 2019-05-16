<?php
declare(strict_types=1);

namespace Lithos\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
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

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();

        if ($request->attributes->get('isOrganizationContext') === true) {
            $organization = $request->attributes->get('organization');
            $this->twig->addGlobal('organization', $organization);
        }

        if ($request->attributes->has('person')) {
            $person = $request->attributes->get('person');
            $this->twig->addGlobal('person', $person);
        }

        $this->twig->addGlobal('asset_domain', $this->assetDomain);
    }
}
