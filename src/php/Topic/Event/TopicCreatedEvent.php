<?php
declare(strict_types=1);

namespace Hipper\Topic\Event;

use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Person\PersonModel;
use Hipper\Topic\TopicModel;
use Symfony\Contracts\EventDispatcher\Event;

class TopicCreatedEvent extends Event
{
    public const NAME = 'topic.created';

    protected TopicModel $topic;
    protected KnowledgebaseOwnerModelInterface $knowledgebaseOwner;
    protected KnowledgebaseRouteModel $route;
    protected PersonModel $creator;

    public function __construct(
        TopicModel $topic,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        KnowledgebaseRouteModel $route,
        PersonModel $creator
    ) {
        $this->topic = $topic;
        $this->knowledgebaseOwner = $knowledgebaseOwner;
        $this->route = $route;
        $this->creator = $creator;
    }

    public function getTopic(): TopicModel
    {
        return $this->topic;
    }

    public function getKnowledgebaseOwner(): KnowledgebaseOwnerModelInterface
    {
        return $this->knowledgebaseOwner;
    }

    public function getRoute(): KnowledgebaseRouteModel
    {
        return $this->route;
    }

    public function getCreator(): PersonModel
    {
        return $this->creator;
    }
}
