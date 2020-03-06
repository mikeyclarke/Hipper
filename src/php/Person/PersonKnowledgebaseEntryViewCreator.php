<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\Document\DocumentModel;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseContentTypeException;
use Hipper\Knowledgebase\KnowledgebaseContentModelInterface;
use Hipper\Person\Storage\PersonKnowledgebaseEntryViewInserter;
use Hipper\Topic\TopicModel;

class PersonKnowledgebaseEntryViewCreator
{
    private IdGenerator $idGenerator;
    private PersonKnowledgebaseEntryViewInserter $inserter;

    public function __construct(
        IdGenerator $idGenerator,
        PersonKnowledgebaseEntryViewInserter $inserter
    ) {
        $this->idGenerator = $idGenerator;
        $this->inserter = $inserter;
    }

    public function create(PersonModel $person, KnowledgebaseContentModelInterface $entry): void
    {
        $id = $this->idGenerator->generate();
        $documentId = null;
        $topicId = null;

        $class = get_class($entry);
        switch ($class) {
            case DocumentModel::class:
                $documentId = $entry->getId();
                break;
            case TopicModel::class:
                $topicId = $entry->getId();
                break;
            default:
                throw new UnsupportedKnowledgebaseContentTypeException;
        }

        $this->inserter->insert(
            $id,
            $person->getId(),
            $entry->getKnowledgebaseId(),
            $person->getOrganizationId(),
            $documentId,
            $topicId
        );
    }
}
