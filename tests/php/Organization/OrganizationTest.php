<?php
declare(strict_types=1);

namespace Hipper\Tests\Organization;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Organization\Organization;
use Hipper\Organization\OrganizationInserter;
use Hipper\Organization\OrganizationModel;
use Hipper\Organization\OrganizationRepository;
use Hipper\Organization\OrganizationUpdater;
use Hipper\Organization\OrganizationValidator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class OrganizationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $idGenerator;
    private $organizationInserter;
    private $organizationRepository;
    private $organizationUpdater;
    private $organizationValidator;
    private $organization;

    public function setUp(): void
    {
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->organizationInserter = m::mock(OrganizationInserter::class);
        $this->organizationRepository = m::mock(OrganizationRepository::class);
        $this->organizationUpdater = m::mock(OrganizationUpdater::class);
        $this->organizationValidator = m::mock(OrganizationValidator::class);

        $this->organization = new Organization(
            $this->idGenerator,
            $this->organizationInserter,
            $this->organizationRepository,
            $this->organizationUpdater,
            $this->organizationValidator
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $organizationId = 'org-uuid';
        $organizationName = 'Unnamed Organization';
        $organizationResult = [
            'id' => $organizationId,
            'name' => $organizationName,
        ];

        $this->createIdGeneratorExpectation($organizationId);
        $this->createOrganizationInserterExpectation([$organizationId, $organizationName], $organizationResult);

        $expectedId = $organizationId;
        $expectedName = $organizationName;

        $result = $this->organization->create();
        $this->assertInstanceOf(OrganizationModel::class, $result);
        $this->assertEquals($expectedId, $result->getId());
        $this->assertEquals($expectedName, $result->getName());
    }

    /**
     * @test
     */
    public function update()
    {
        $organizationId = 'org-uuid';
        $properties = ['properties'];

        $this->createOrganizationValidatorExpectation([$properties]);
        $this->createOrganizationUpdaterExpectation([$organizationId, $properties]);

        $this->organization->update($organizationId, $properties);
    }

    private function createOrganizationUpdaterExpectation($args)
    {
        $this->organizationUpdater
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

    private function createOrganizationInserterExpectation($args, $result)
    {
        $this->organizationInserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createIdGeneratorExpectation($result)
    {
        $this->idGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
    }
}
