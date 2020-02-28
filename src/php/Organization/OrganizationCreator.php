<?php
declare(strict_types=1);

namespace Hipper\Organization;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Organization\Storage\OrganizationInserter;

class OrganizationCreator
{
    private IdGenerator $idGenerator;
    private OrganizationInserter $organizationInserter;

    public function __construct(
        IdGenerator $idGenerator,
        OrganizationInserter $organizationInserter
    ) {
        $this->idGenerator = $idGenerator;
        $this->organizationInserter = $organizationInserter;
    }

    public function create(): OrganizationModel
    {
        $organization = $this->organizationInserter->insert(
            $this->idGenerator->generate(),
            OrganizationModel::DEFAULT_NAME
        );
        $model = OrganizationModel::createFromArray($organization);
        return $model;
    }
}
