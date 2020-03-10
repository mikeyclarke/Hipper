<?php
declare(strict_types=1);

namespace Hipper\Document\Event;

use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Person\PersonModel;
use Hipper\Document\DocumentModel;
use Symfony\Contracts\EventDispatcher\Event;

class DocumentCreatedEvent extends Event
{
    public const NAME = 'document.created';

    protected DocumentModel $document;
    protected KnowledgebaseOwnerModelInterface $knowledgebaseOwner;
    protected KnowledgebaseRouteModel $route;
    protected PersonModel $creator;

    public function __construct(
        DocumentModel $document,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        KnowledgebaseRouteModel $route,
        PersonModel $creator
    ) {
        $this->document = $document;
        $this->knowledgebaseOwner = $knowledgebaseOwner;
        $this->route = $route;
        $this->creator = $creator;
    }

    public function getDocument(): DocumentModel
    {
        return $this->document;
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
