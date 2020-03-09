<?php
declare(strict_types=1);

namespace Hipper\Topic\Event;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Activity\ActivityCreator;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Hipper\Topic\Event\TopicCreatedEvent;
use Hipper\Topic\TopicModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TopicSubscriber implements EventSubscriberInterface
{
    private const TOPIC_CREATED_ACTIVITY_TYPE = 'topic_created';

    private ActivityCreator $activityCreator;

    public function __construct(
        ActivityCreator $activityCreator
    ) {
        $this->activityCreator = $activityCreator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TopicCreatedEvent::NAME => 'onTopicCreated',
        ];
    }

    public function onTopicCreated(TopicCreatedEvent $event): void
    {
        $topic = $event->getTopic();
        $knowledgebaseOwner = $event->getKnowledgebaseOwner();
        $route = $event->getRoute();

        $knowledgebaseOwnerName = $knowledgebaseOwner->getName();
        $knowledgebaseOwnerUrlId = null;
        $teamId = null;
        $projectId = null;

        $class = get_class($knowledgebaseOwner);
        switch ($class) {
            case TeamModel::class:
                $knowledgebaseOwnerType = 'team';
                $teamId = $knowledgebaseOwner->getId();
                $knowledgebaseOwnerUrlId = $knowledgebaseOwner->getUrlId();
                break;
            case ProjectModel::class:
                $knowledgebaseOwnerType = 'project';
                $projectId = $knowledgebaseOwner->getId();
                $knowledgebaseOwnerUrlId = $knowledgebaseOwner->getUrlId();
                break;
            case OrganizationModel::class:
                $knowledgebaseOwnerType = 'organization';
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        $properties = [
            'knowledgebase_owner_type' => $knowledgebaseOwnerType,
            'knowledgebase_owner_name' => $knowledgebaseOwnerName,
            'knowledgebase_owner_url_id' => $knowledgebaseOwnerUrlId,
            'topic_name' => $topic->getName(),
            'topic_description' => $this->getTopicDescription($topic),
            'topic_url_id' => $route->getUrlId(),
            'topic_route' => $route->getRoute(),
        ];

        $this->activityCreator->create(
            $event->getCreator(),
            self::TOPIC_CREATED_ACTIVITY_TYPE,
            $properties,
            null,
            $topic->getId(),
            $teamId,
            $projectId,
        );
    }

    private function getTopicDescription(TopicModel $topic): string
    {
        if (null !== $topic->getDescription() && !empty($topic->getDescription())) {
            return $topic->getDescription();
        }

        return 'This topic doesn’t have a description yet';
    }
}
