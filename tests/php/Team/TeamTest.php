<?php
declare(strict_types=1);

namespace Hipper\Tests\Team;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\Knowledgebase;
use Hipper\Person\PersonModel;
use Hipper\Team\PersonToTeamMapInserter;
use Hipper\Team\Team;
use Hipper\Team\TeamInserter;
use Hipper\Team\TeamModel;
use Hipper\Team\TeamModelMapper;
use Hipper\Team\TeamValidator;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $idGenerator;
    private $knowledgebase;
    private $personToTeamMapInserter;
    private $teamInserter;
    private $teamModelMapper;
    private $teamValidator;
    private $urlSlugGenerator;
    private $team;
    private $person;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebase = m::mock(Knowledgebase::class);
        $this->personToTeamMapInserter = m::mock(PersonToTeamMapInserter::class);
        $this->teamInserter = m::mock(TeamInserter::class);
        $this->teamModelMapper = m::mock(TeamModelMapper::class);
        $this->teamValidator = m::mock(TeamValidator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->team = new Team(
            $this->connection,
            $this->idGenerator,
            $this->knowledgebase,
            $this->personToTeamMapInserter,
            $this->teamInserter,
            $this->teamModelMapper,
            $this->teamValidator,
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
            'name' => 'Engineering',
            'description' => 'Our Engineering team executes our vision and brings Hipper’s products to life',
        ];

        $teamId = 'team-uuid';
        $teamSlug = 'engineering';
        $knowledgebaseResult = ['id' => 'kb-uuid'];
        $teamInserterArgs = [
            $teamId,
            $parameters['name'],
            $parameters['description'],
            $teamSlug,
            $knowledgebaseResult['id'],
            $this->person->getOrganizationId()
        ];
        $teamResult = ['id' => $teamId];
        $personToTeamMapId = 'person-to-team-map-uuid';
        $teamModel = new TeamModel;

        $this->createTeamValidatorExpectation([$parameters, $this->person->getOrganizationId(), true]);
        $this->createIdGeneratorExpectation($teamId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $teamSlug);
        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseExpectation([$this->person->getOrganizationId()], $knowledgebaseResult);
        $this->createTeamInserterExpectation($teamInserterArgs, $teamResult);
        $this->createIdGeneratorExpectation($personToTeamMapId);
        $this->createPersonToTeamMapInserterExpectation([$personToTeamMapId, $this->person->getId(), $teamId]);
        $this->createConnectionCommitExpectation();
        $this->createTeamModelMapperExpectation([$teamResult], $teamModel);

        $expected = $teamModel;

        $result = $this->team->create($this->person, $parameters);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function connectionIsRolledBackOnException()
    {
        $parameters = [
            'name' => 'Engineering',
            'description' => 'Our Engineering team executes our vision and brings Hipper’s products to life',
        ];

        $teamId = 'team-uuid';
        $teamSlug = 'engineering';
        $knowledgebaseResult = ['id' => 'kb-uuid'];
        $teamInserterArgs = [
            $teamId,
            $parameters['name'],
            $parameters['description'],
            $teamSlug,
            $knowledgebaseResult['id'],
            $this->person->getOrganizationId()
        ];

        $this->createTeamValidatorExpectation([$parameters, $this->person->getOrganizationId(), true]);
        $this->createIdGeneratorExpectation($teamId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $teamSlug);
        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseExpectation([$this->person->getOrganizationId()], $knowledgebaseResult);
        $this->teamInserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$teamInserterArgs)
            ->andThrow(\Exception::class);
        $this->createConnectionRollbackExpectation();

        $this->expectException(\Exception::class);

        $this->team->create($this->person, $parameters);
    }

    private function createConnectionRollbackExpectation()
    {
        $this->connection
            ->shouldReceive('rollBack')
            ->once();
    }

    private function createTeamModelMapperExpectation($args, $result)
    {
        $this->teamModelMapper
            ->shouldReceive('createFromArray')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createConnectionCommitExpectation()
    {
        $this->connection
            ->shouldReceive('commit')
            ->once();
    }

    private function createPersonToTeamMapInserterExpectation($args)
    {
        $this->personToTeamMapInserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args);
    }

    private function createTeamInserterExpectation($args, $result)
    {
        $this->teamInserter
            ->shouldReceive('insert')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseExpectation($args, $result)
    {
        $this->knowledgebase
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

    private function createTeamValidatorExpectation($args)
    {
        $this->teamValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
