<?php
declare(strict_types=1);

namespace Hipper\Organization;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseCreator;
use Hipper\Organization\Storage\OrganizationInserter;
use Hipper\Organization\Storage\OrganizationUpdater as OrganizationStorageUpdater;

class OrganizationCreator
{
    private IdGenerator $idGenerator;
    private KnowledgebaseCreator $knowledgebaseCreator;
    private OrganizationInserter $organizationInserter;
    private OrganizationStorageUpdater $organizationStorageUpdater;

    public function __construct(
        IdGenerator $idGenerator,
        KnowledgebaseCreator $knowledgebaseCreator,
        OrganizationInserter $organizationInserter,
        OrganizationStorageUpdater $organizationStorageUpdater
    ) {
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseCreator = $knowledgebaseCreator;
        $this->organizationInserter = $organizationInserter;
        $this->organizationStorageUpdater = $organizationStorageUpdater;
    }

    public function create(): OrganizationModel
    {
        $result = $this->organizationInserter->insert(
            $this->idGenerator->generate(),
            OrganizationModel::DEFAULT_NAME
        );
        $organizationId = $result['id'];
        $knowledgebase = $this->knowledgebaseCreator->create('organization', $organizationId);

        $result = $this->organizationStorageUpdater->update(
            $organizationId,
            ['knowledgebase_id' => $knowledgebase['id']]
        );

        $organization = OrganizationModel::createFromArray($result);

        return $organization;
    }
}
