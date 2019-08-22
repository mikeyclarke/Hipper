<?php
declare(strict_types=1);

namespace Hipper\Subscriber;

use Hipper\Organization\OrganizationModel;
use Hipper\Organization\OrganizationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class OrganizationContextSubscriber implements EventSubscriberInterface
{
    private $organizationRepository;

    public function __construct(
        OrganizationRepository $organizationRepository
    ) {
        $this->organizationRepository = $organizationRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }

        $request = $event->getRequest();
        $host = $request->getHost();
        $parts = explode('.', $host);

        if (!$this->isOrganizationContext($parts)) {
            $request->attributes->set('isOrganizationContext', false);
            return;
        }

        $organization = $this->getOrganizationFromHost($parts);
        if (null === $organization) {
            throw new NotFoundHttpException;
        }

        $request->attributes->set('isOrganizationContext', true);
        $request->attributes->set('organization', $organization);
    }

    private function getOrganizationFromHost(array $hostParts): ?OrganizationModel
    {
        $result = $this->organizationRepository->findBySubdomain($hostParts[0]);
        if (null === $result) {
            return null;
        }
        return OrganizationModel::createFromArray($result);
    }

    private function isOrganizationContext(array $hostParts): bool
    {
        return count($hostParts) === 3;
    }
}
