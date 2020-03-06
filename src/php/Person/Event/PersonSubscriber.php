<?php
declare(strict_types=1);

namespace Hipper\Person\Event;

use Hipper\Activity\ActivityCreator;
use Hipper\Person\Event\PersonCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PersonSubscriber implements EventSubscriberInterface
{
    private const PERSON_CREATED_ACTIVITY_TYPE = 'person_joined_organization';

    private ActivityCreator $activityCreator;

    public function __construct(
        ActivityCreator $activityCreator
    ) {
        $this->activityCreator = $activityCreator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PersonCreatedEvent::NAME => 'onPersonCreated',
        ];
    }

    public function onPersonCreated(PersonCreatedEvent $event): void
    {
        $this->activityCreator->create($event->getPerson(), self::PERSON_CREATED_ACTIVITY_TYPE);
    }
}
