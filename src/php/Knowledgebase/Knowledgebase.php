<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\IdGenerator\IdGenerator;

class Knowledgebase
{
    private $idGenerator;
    private $knowledgebaseInserter;

    public function __construct(
        IdGenerator $idGenerator,
        KnowledgebaseInserter $knowledgebaseInserter
    ) {
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseInserter = $knowledgebaseInserter;
    }

    public function create(string $organizationId): array
    {
        $id = $this->idGenerator->generate();
        return $this->knowledgebaseInserter->insert($id, $organizationId);
    }
}
