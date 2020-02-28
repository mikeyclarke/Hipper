<?php
declare(strict_types=1);

namespace Hipper\Tests\Organization;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Organization\OrganizationCreator;
use Hipper\Organization\OrganizationModel;
use Hipper\Organization\Storage\OrganizationInserter;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class OrganizationCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $idGenerator;
    private $organizationInserter;
    private $organizationCreator;

    public function setUp(): void
    {
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->organizationInserter = m::mock(OrganizationInserter::class);

        $this->organizationCreator = new OrganizationCreator(
            $this->idGenerator,
            $this->organizationInserter
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $organizationId = 'org-uuid';
        $organizationName = OrganizationModel::DEFAULT_NAME;
        $organizationResult = [
            'id' => $organizationId,
            'name' => $organizationName,
        ];

        $this->createIdGeneratorExpectation($organizationId);
        $this->createOrganizationInserterExpectation([$organizationId, $organizationName], $organizationResult);

        $expectedId = $organizationId;
        $expectedName = $organizationName;

        $result = $this->organizationCreator->create();
        $this->assertInstanceOf(OrganizationModel::class, $result);
        $this->assertEquals($expectedId, $result->getId());
        $this->assertEquals($expectedName, $result->getName());
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
