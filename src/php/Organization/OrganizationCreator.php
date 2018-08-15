<?php
namespace hleo\Organization;

use hleo\IdGenerator\IdGenerator;

class OrganizationCreator
{
    const DEFAULT_NAME = 'Unnamed Organization';

    private $idGenerator;
    private $organizationInserter;

    public function __construct(
        IdGenerator $idGenerator,
        OrganizationInserter $organizationInserter
    ) {
        $this->idGenerator = $idGenerator;
        $this->organizationInserter = $organizationInserter;
    }

    public function create()
    {
        $organization = $this->organizationInserter->insert(
            $this->idGenerator->generate(),
            self::DEFAULT_NAME
        );

        return $organization;
    }
}
