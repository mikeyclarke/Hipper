<?php
declare(strict_types=1);

namespace Hipper\Document\Event;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Activity\ActivityCreator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Hipper\Document\Event\DocumentCreatedEvent;
use Hipper\Document\DocumentModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DocumentSubscriber implements EventSubscriberInterface
{
    private const DOCUMENT_CREATED_ACTIVITY_TYPE = 'document_created';

    private ActivityCreator $activityCreator;

    public function __construct(
        ActivityCreator $activityCreator
    ) {
        $this->activityCreator = $activityCreator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DocumentCreatedEvent::NAME => 'onDocumentCreated',
        ];
    }

    public function onDocumentCreated(DocumentCreatedEvent $event): void
    {
        $document = $event->getDocument();
        $knowledgebaseOwner = $event->getKnowledgebaseOwner();
        $route = $event->getRoute();

        $knowledgebaseOwnerName = $knowledgebaseOwner->getName();
        $knowledgebaseOwnerUrlSlug = null;
        $teamId = null;
        $projectId = null;

        $class = get_class($knowledgebaseOwner);
        switch ($class) {
            case TeamModel::class:
                $knowledgebaseOwnerType = 'team';
                $teamId = $knowledgebaseOwner->getId();
                $knowledgebaseOwnerUrlSlug = $knowledgebaseOwner->getUrlSlug();
                break;
            case ProjectModel::class:
                $knowledgebaseOwnerType = 'project';
                $projectId = $knowledgebaseOwner->getId();
                $knowledgebaseOwnerUrlSlug = $knowledgebaseOwner->getUrlSlug();
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
            'knowledgebase_owner_url_slug' => $knowledgebaseOwnerUrlSlug,
            'document_name' => $document->getName(),
            'document_description' => $this->getDocumentDescription($document),
            'document_url_id' => $route->getUrlId(),
            'document_route' => $route->getRoute(),
        ];

        $this->activityCreator->create(
            $event->getCreator(),
            self::DOCUMENT_CREATED_ACTIVITY_TYPE,
            $properties,
            $document->getId(),
            null,
            $teamId,
            $projectId,
        );
    }

    private function getDocumentDescription(DocumentModel $document): string
    {
        if (null !== $document->getDescription() && !empty($document->getDescription())) {
            return $document->getDescription();
        }

        if (null !== $document->getDeducedDescription() && !empty($document->getDeducedDescription())) {
            return $document->getDeducedDescription();
        }

        return 'This document doesn’t have a description yet';
    }
}
