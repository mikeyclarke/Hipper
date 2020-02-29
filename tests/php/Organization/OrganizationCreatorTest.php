<?php
declare(strict_types=1);

namespace Hipper\Tests\Organization;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseCreator;
use Hipper\Organization\OrganizationCreator;
use Hipper\Organization\OrganizationModel;
use Hipper\Organization\Storage\OrganizationInserter;
use Hipper\Organization\Storage\OrganizationUpdater as OrganizationStorageUpdater;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class OrganizationCreatorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $idGenerator;
    private $knowledgebaseCreator;
    private $organizationInserter;
    private $organizationCreator;
    private $organizationStorageUpdater;

    public function setUp(): void
    {
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebaseCreator = m::mock(KnowledgebaseCreator::class);
        $this->organizationInserter = m::mock(OrganizationInserter::class);
        $this->organizationStorageUpdater = m::mock(OrganizationStorageUpdater::class);

        $this->organizationCreator = new OrganizationCreator(
            $this->idGenerator,
            $this->knowledgebaseCreator,
            $this->organizationInserter,
            $this->organizationStorageUpdater
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $organizationId = 'org-uuid';
        $organizationName = OrganizationModel::DEFAULT_NAME;
        $knowledgebaseId = 'kb-uuid';
        $organizationResult = [
            'id' => $organizationId,
            'name' => $organizationName,
        ];
        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $updatedOrganizationResult = [
            'id' => $organizationId,
            'name' => $organizationName,
            'knowledgebase_id' => $knowledgebaseId,
        ];

        $this->createIdGeneratorExpectation($organizationId);
        $this->createOrganizationInserterExpectation([$organizationId, $organizationName], $organizationResult);
        $this->createKnowledgebaseCreatorExpectation(['organization', $organizationId], $knowledgebaseResult);
        $this->createOrganizationStorageUpdaterExpectation(
            [$organizationId, ['knowledgebase_id' => $knowledgebaseId]],
            $updatedOrganizationResult
        );

        $expectedId = $organizationId;
        $expectedName = $organizationName;

        $result = $this->organizationCreator->create();
        $this->assertInstanceOf(OrganizationModel::class, $result);
        $this->assertEquals($expectedId, $result->getId());
        $this->assertEquals($expectedName, $result->getName());
        $this->assertEquals($knowledgebaseId, $result->getKnowledgebaseId());
    }

    private function createOrganizationStorageUpdaterExpectation($args, $result)
    {
        $this->organizationStorageUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseCreatorExpectation($args, $result)
    {
        $this->knowledgebaseCreator
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
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
