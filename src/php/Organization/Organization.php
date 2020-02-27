<?php
declare(strict_types=1);

namespace Hipper\Organization;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Organization\Storage\OrganizationInserter;
use Hipper\Organization\Storage\OrganizationUpdater;

class Organization
{
    const DEFAULT_NAME = 'Unnamed Organization';

    private $idGenerator;
    private $organizationInserter;
    private $organizationRepository;
    private $organizationUpdater;
    private $organizationValidator;

    public function __construct(
        IdGenerator $idGenerator,
        OrganizationInserter $organizationInserter,
        OrganizationRepository $organizationRepository,
        OrganizationUpdater $organizationUpdater,
        OrganizationValidator $organizationValidator
    ) {
        $this->idGenerator = $idGenerator;
        $this->organizationInserter = $organizationInserter;
        $this->organizationRepository = $organizationRepository;
        $this->organizationUpdater = $organizationUpdater;
        $this->organizationValidator = $organizationValidator;
    }

    public function get(string $id): ?OrganizationModel
    {
        $result = $this->organizationRepository->findById($id);
        if (null === $result) {
            return $result;
        }
        $model = OrganizationModel::createFromArray($result);
        return $model;
    }

    public function create(): OrganizationModel
    {
        $organization = $this->organizationInserter->insert(
            $this->idGenerator->generate(),
            self::DEFAULT_NAME
        );
        $model = OrganizationModel::createFromArray($organization);
        return $model;
    }

    public function update(string $organizationId, array $properties): void
    {
        $this->organizationValidator->validate($properties);
        $this->organizationUpdater->update($organizationId, $properties);
    }
}
