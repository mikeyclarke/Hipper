<?php
declare(strict_types=1);

namespace Lithos\Organization;

use Lithos\IdGenerator\IdGenerator;

class Organization
{
    const DEFAULT_NAME = 'Unnamed Organization';

    private $idGenerator;
    private $organizationInserter;
    private $organizationModelMapper;
    private $organizationValidator;

    public function __construct(
        IdGenerator $idGenerator,
        OrganizationInserter $organizationInserter,
        OrganizationModelMapper $organizationModelMapper,
        OrganizationValidator $organizationValidator
    ) {
        $this->idGenerator = $idGenerator;
        $this->organizationInserter = $organizationInserter;
        $this->organizationModelMapper = $organizationModelMapper;
        $this->organizationValidator = $organizationValidator;
    }

    public function create(): OrganizationModel
    {
        $organization = $this->organizationInserter->insert(
            $this->idGenerator->generate(),
            self::DEFAULT_NAME
        );
        $model = $this->organizationModelMapper->createFromArray($organization);
        return $model;
    }

    public function update(string $organizationId, array $properties): void
    {
        $this->organizationValidator->validate($properties);
        $this->organizationUpdater->update($organizationId, $properties);
    }
}
