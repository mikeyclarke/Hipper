<?php
declare(strict_types=1);

namespace Hipper\Tests\Project;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseCreator;
use Hipper\Person\PersonModel;
use Hipper\Project\Project;
use Hipper\Project\ProjectModel;
use Hipper\Project\ProjectValidator;
use Hipper\Project\Storage\PersonToProjectMapInserter;
use Hipper\Project\Storage\ProjectInserter;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ProjectTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $idGenerator;
    private $knowledgebaseCreator;
    private $personToProjectMapInserter;
    private $projectInserter;
    private $projectValidator;
    private $urlSlugGenerator;
    private $project;
    private $person;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebaseCreator = m::mock(KnowledgebaseCreator::class);
        $this->personToProjectMapInserter = m::mock(PersonToProjectMapInserter::class);
        $this->projectInserter = m::mock(ProjectInserter::class);
        $this->projectValidator = m::mock(ProjectValidator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->project = new Project(
            $this->connection,
            $this->idGenerator,
            $this->knowledgebaseCreator,
            $this->personToProjectMapInserter,
            $this->projectInserter,
            $this->projectValidator,
            $this->urlSlugGenerator
        );

        $this->person = new PersonModel;
        $this->person->setId('person-uuid');
        $this->person->setOrganizationId('org-uuid');
    }

    /**
     * @test
     */
    public function create()
    {
        $parameters = [
            'name' => 'Marketing website',
            'description' =>
                'Growing our customerbase by creating an ever better representation of our product and our company',
        ];

        $projectId = 'project-uuid';
        $projectSlug = 'marketing-website';
        $knowledgebaseResult = ['id' => 'kb-uuid'];
        $projectInserterArgs = [
            $projectId,
            $parameters['name'],
            $parameters['description'],
            $projectSlug,
            $knowledgebaseResult['id'],
            $this->person->getOrganizationId(),
        ];
        $projectResult = ['id' => $projectId, 'name' => $parameters['name'], 'url_id' => $projectSlug];
        $personToProjectMapId = 'map-uuid';

        $this->createProjectValidatorExpectation([$parameters, $this->person->getOrganizationId(), true]);
        $this->createIdGeneratorExpectation($projectId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $projectSlug);
        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseCreatorExpectation(
            ['project', $this->person->getOrganizationId()],
            $knowledgebaseResult
        );
        $this->createProjectInserterExpectation($projectInserterArgs, $projectResult);
        $this->createIdGeneratorExpectation($personToProjectMapId);
        $this->createPersonToProjectMapInserterExpectation([$personToProjectMapId, $this->person->getId(), $projectId]);
        $this->createConnectionCommitExpectation();

        $result = $this->project->create($this->person, $parameters);
        $this->assertInstanceOf(ProjectModel::class, $result);
        $this->assertEquals($projectId, $result->getId());
    }

    /**
     * @test
     */
    public function connectionIsRolledBackOnException()
    {
        $parameters = [
            'name' => 'Marketing website',
            'description' =>
                'Growing our customerbase by creating an ever better representation of our product and our company',
        ];

        $projectId = 'project-uuid';
        $projectSlug = 'marketing-website';
        $knowledgebaseResult = ['id' => 'kb-uuid'];
        $projectInserterArgs = [
            $projectId,
            $parameters['name'],
            $parameters['description'],
            $projectSlug,
            $knowledgebaseResult['id'],
            $this->person->getOrganizationId(),
        ];

        $this->createProjectValidatorExpectation([$parameters, $this->person->getOrganizationId(), true]);
        $this->createIdGeneratorExpectation($projectId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $projectSlug);
        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseCreatorExpectation(
            ['project', $this->person->getOrganizationId()],
            $knowledgebaseResult
        );
        $this->projectInserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$projectInserterArgs)
            ->andThrow(\Exception::class);
        $this->createConnectionRollBackExpectation();

        $this->expectException(\Exception::class);

        $this->project->create($this->person, $parameters);
    }

    private function createConnectionRollBackExpectation()
    {
        $this->connection
            ->shouldReceive('rollBack')
            ->once();
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createPersonToProjectMapInserterExpectation($args)
    {
        $this->personToProjectMapInserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args);
    }

    private function createProjectInserterExpectation($args, $result)
    {
        $this->projectInserter
            ->shouldReceive('insert')
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

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createUrlSlugGeneratorExpectation($args, $result)
    {
        $this->urlSlugGenerator
            ->shouldReceive('generateFromString')
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

    private function createProjectValidatorExpectation($args)
    {
        $this->projectValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
