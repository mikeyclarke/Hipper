<?php
declare(strict_types=1);

namespace Hipper\Tests\Knowledgebase;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Project\ProjectModel;
use Hipper\Project\ProjectRepository;
use Hipper\Team\TeamModel;
use Hipper\Team\TeamRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class KnowledgebaseOwnerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $projectRepository;
    private $teamRepository;
    private $knowledgebaseOwner;
    private $knowledgebase;

    public function setUp(): void
    {
        $this->projectRepository = m::mock(ProjectRepository::class);
        $this->teamRepository = m::mock(TeamRepository::class);

        $this->knowledgebaseOwner = new KnowledgebaseOwner(
            $this->projectRepository,
            $this->teamRepository
        );

        $this->knowledgebase = new KnowledgebaseModel;
        $this->knowledgebase->setId('kb-uuid');
        $this->knowledgebase->setOrganizationId('org-uuid');
    }

    /**
     * @test
     */
    public function getWithKnowledgebaseBelongingToTeam()
    {
        $this->knowledgebase->setEntity('team');

        $teamRow = [
            'id' => 'team-uuid',
            'knowledgebase_id' => 'kb-uuid',
        ];

        $this->createTeamRepositoryExpectation(
            [$this->knowledgebase->getId(), $this->knowledgebase->getOrganizationId()],
            $teamRow
        );

        $result = $this->knowledgebaseOwner->get($this->knowledgebase);
        $this->assertInstanceOf(TeamModel::class, $result);
    }

    /**
     * @test
     */
    public function getWithKnowledgebaseBelongingToProject()
    {
        $this->knowledgebase->setEntity('project');

        $projectRow = [
            'id' => 'project-uuid',
            'knowledgebase_id' => 'kb-uuid',
        ];

        $this->createProjectRepositoryExpectation(
            [$this->knowledgebase->getId(), $this->knowledgebase->getOrganizationId()],
            $projectRow
        );

        $result = $this->knowledgebaseOwner->get($this->knowledgebase);
        $this->assertInstanceOf(ProjectModel::class, $result);
    }

    /**
     * @test
     */
    public function getWithKnowledgebaseBelongingToUnknownEntity()
    {
        $this->knowledgebase->setEntity('foo');

        $this->expectException(UnsupportedKnowledgebaseEntityException::class);

        $this->knowledgebaseOwner->get($this->knowledgebase);
    }

    private function createProjectRepositoryExpectation($args, $result)
    {
        $this->projectRepository
            ->shouldReceive('findByKnowledgebaseId')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createTeamRepositoryExpectation($args, $result)
    {
        $this->teamRepository
            ->shouldReceive('findByKnowledgebaseId')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
