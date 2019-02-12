<?php
declare(strict_types=1);

namespace Lithos\Subscriber;

use Lithos\Organization\OrganizationModel;
use Lithos\Organization\OrganizationModelMapper;
use Lithos\Organization\OrganizationRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class OrganizationContextSubscriber implements EventSubscriberInterface
{
    private $organizationModelMapper;
    private $organizationRepository;

    public function __construct(
        OrganizationModelMapper $organizationModelMapper,
        OrganizationRepository $organizationRepository
    ) {
        $this->organizationModelMapper = $organizationModelMapper;
        $this->organizationRepository = $organizationRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1],
        ];
    }

    public function onKernelRequest(GetResponseEvent $event): void
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
        return $this->organizationModelMapper->createFromArray($result);
    }

    private function isOrganizationContext(array $hostParts): bool
    {
        return count($hostParts) === 3;
    }
}
