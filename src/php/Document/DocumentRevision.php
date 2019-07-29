<?php
declare(strict_types=1);

namespace Lithos\Document;

use Lithos\IdGenerator\IdGenerator;

class DocumentRevision
{
    private $documentRevisionInserter;
    private $idGenerator;

    public function __construct(
        DocumentRevisionInserter $documentRevisionInserter,
        IdGenerator $idGenerator
    ) {
        $this->documentRevisionInserter = $documentRevisionInserter;
        $this->idGenerator = $idGenerator;
    }

    public function create(DocumentModel $document): void
    {
        $id = $this->idGenerator->generate();
        $this->documentRevisionInserter->insert(
            $id,
            $document->getId(),
            $document->getName(),
            $document->getOrganizationId(),
            $document->getKnowledgebaseId(),
            $document->getCreatedBy(),
            $document->getDescription(),
            $document->getDeducedDescription(),
            $document->getContent()
        );
    }
}
