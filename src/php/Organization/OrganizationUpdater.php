<?php
declare(strict_types=1);

namespace Hipper\Organization;

use Hipper\Organization\Storage\OrganizationUpdater as OrganizationStorageUpdater;

class OrganizationUpdater
{
    private OrganizationStorageUpdater $organizationStorageUpdater;
    private OrganizationValidator $organizationValidator;

    public function __construct(
        OrganizationStorageUpdater $organizationStorageUpdater,
        OrganizationValidator $organizationValidator
    ) {
        $this->organizationStorageUpdater = $organizationStorageUpdater;
        $this->organizationValidator = $organizationValidator;
    }

    public function update(string $organizationId, array $properties): void
    {
        $this->organizationValidator->validate($properties);
        $this->organizationStorageUpdater->update($organizationId, $properties);
    }
}
