<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;

class Knowledgebase
{
    const SUPPORTED_ENTITIES = ['team', 'project'];

    private $idGenerator;
    private $knowledgebaseInserter;

    public function __construct(
        IdGenerator $idGenerator,
        KnowledgebaseInserter $knowledgebaseInserter
    ) {
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseInserter = $knowledgebaseInserter;
    }

    public function create(string $entity, string $organizationId): array
    {
        if (!in_array($entity, self::SUPPORTED_ENTITIES)) {
            throw new UnsupportedKnowledgebaseEntityException;
        }

        $id = $this->idGenerator->generate();
        return $this->knowledgebaseInserter->insert($id, $entity, $organizationId);
    }
}
