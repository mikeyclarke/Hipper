<?php
declare(strict_types=1);

namespace Hipper\Organization\Event;

use Hipper\Activity\ActivityCreator;
use Hipper\Organization\Event\OrganizationCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrganizationSubscriber implements EventSubscriberInterface
{
    private const ORGANIZATION_CREATED_ACTIVITY_TYPE = 'organization_created';

    private ActivityCreator $activityCreator;

    public function __construct(
        ActivityCreator $activityCreator
    ) {
        $this->activityCreator = $activityCreator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrganizationCreatedEvent::NAME => 'onOrganizationCreated',
        ];
    }

    public function onOrganizationCreated(OrganizationCreatedEvent $event): void
    {
        $this->activityCreator->create($event->getCreator(), self::ORGANIZATION_CREATED_ACTIVITY_TYPE);
    }
}
