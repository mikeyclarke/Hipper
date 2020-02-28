<?php
declare(strict_types=1);

namespace Hipper\Tests\Organization;

use Hipper\Organization\OrganizationModel;
use Hipper\Organization\OrganizationUpdater;
use Hipper\Organization\OrganizationValidator;
use Hipper\Organization\Storage\OrganizationUpdater as OrganizationStorageUpdater;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class OrganizationUpdaterTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $organizationStorageUpdater;
    private $organizationValidator;
    private $organizationUpdater;

    public function setUp(): void
    {
        $this->organizationStorageUpdater = m::mock(OrganizationStorageUpdater::class);
        $this->organizationValidator = m::mock(OrganizationValidator::class);

        $this->organizationUpdater = new OrganizationUpdater(
            $this->organizationStorageUpdater,
            $this->organizationValidator
        );
    }

    /**
     * @test
     */
    public function update()
    {
        $organizationId = 'org-uuid';
        $properties = ['properties'];

        $this->createOrganizationValidatorExpectation([$properties]);
        $this->createOrganizationStorageUpdaterExpectation([$organizationId, $properties]);

        $this->organizationUpdater->update($organizationId, $properties);
    }

    private function createOrganizationStorageUpdaterExpectation($args)
    {
        $this->organizationStorageUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args);
    }

    private function createOrganizationValidatorExpectation($args)
    {
        $this->organizationValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
