<?php
declare(strict_types=1);

namespace Hipper\Tests\Section;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Person\PersonModel;
use Hipper\Project\ProjectModel;
use Hipper\Section\Section;
use Hipper\Section\SectionInserter;
use Hipper\Section\SectionModel;
use Hipper\Section\SectionValidator;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class SectionTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $idGenerator;
    private $knowledgebaseOwner;
    private $knowledgebaseRepository;
    private $knowledgebaseRoute;
    private $sectionInserter;
    private $sectionValidator;
    private $urlIdGenerator;
    private $urlSlugGenerator;
    private $section;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebaseOwner = m::mock(KnowledgebaseOwner::class);
        $this->knowledgebaseRepository = m::mock(KnowledgebaseRepository::class);
        $this->knowledgebaseRoute = m::mock(KnowledgebaseRoute::class);
        $this->sectionInserter = m::mock(SectionInserter::class);
        $this->sectionValidator = m::mock(SectionValidator::class);
        $this->urlIdGenerator = m::mock(UrlIdGenerator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->section = new Section(
            $this->connection,
            $this->idGenerator,
            $this->knowledgebaseOwner,
            $this->knowledgebaseRepository,
            $this->knowledgebaseRoute,
            $this->sectionInserter,
            $this->sectionValidator,
            $this->urlIdGenerator,
            $this->urlSlugGenerator
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $person = new PersonModel;
        $person->setOrganizationId('org-uuid');
        $parameters = [
            'name' => 'My section',
            'description' => 'My description',
            'knowledgebase_id' => 'kb-uuid',
        ];

        $knowledgebaseResult = [
            'id' => $parameters['knowledgebase_id'],
        ];
        $sectionId = 'section-uuid';
        $sectionUrlSlug = 'my-section';
        $sectionUrlId = 'abcd1234';
        $sectionInserterArgs = [
            $sectionId,
            $parameters['name'],
            $sectionUrlSlug,
            $sectionUrlId,
            $parameters['knowledgebase_id'],
            $person->getOrganizationId(),
            $parameters['description'],
        ];
        $sectionArray = [
            'id' => $sectionId,
            'url_slug' => $sectionUrlSlug,
            'url_id' => $sectionUrlId,
        ];
        $knowledgebaseRouteModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new ProjectModel;

        $this->createKnowledgebaseRepositoryExpectation(
            [$parameters['knowledgebase_id'], 'org-uuid'],
            $knowledgebaseResult
        );
        $this->createSectionValidatorExpectation([$parameters, m::type(KnowledgebaseModel::class), true]);
        $this->createIdGeneratorExpectation($sectionId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $sectionUrlSlug);
        $this->createUrlIdGeneratorExpectation($sectionUrlId);
        $this->createConnectionBeginTransactionExpectation();
        $this->createSectionInserterExpectation($sectionInserterArgs, $sectionArray);
        $this->createKnowledgebaseRouteExpectation(
            [m::type(SectionModel::class), $sectionUrlSlug, true, true],
            $knowledgebaseRouteModel
        );
        $this->createConnectionCommitExpectation();
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);

        $result = $this->section->create($person, $parameters);
        $this->assertIsArray($result);
        $this->assertInstanceOf(SectionModel::class, $result[0]);
        $this->assertEquals($sectionId, $result[0]->getId());
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertInstanceOf(KnowledgebaseOwnerModelInterface::class, $result[2]);
    }

    private function createKnowledgebaseOwnerExpectation($args, $result)
    {
        $this->knowledgebaseOwner
            ->shouldReceive('get')
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

    private function createKnowledgebaseRouteExpectation($args, $result)
    {
        $this->knowledgebaseRoute
            ->shouldReceive('create')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSectionInserterExpectation($args, $result)
    {
        $this->sectionInserter
            ->shouldReceive('insert')
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

    private function createUrlIdGeneratorExpectation($result)
    {
        $this->urlIdGenerator
            ->shouldReceive('generate')
            ->once()
            ->andReturn($result);
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

    private function createKnowledgebaseRepositoryExpectation($args, $result)
    {
        $this->knowledgebaseRepository
            ->shouldReceive('findById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSectionValidatorExpectation($args)
    {
        $this->sectionValidator
            ->shouldReceive('validate')
            ->once()
            ->with(...$args);
    }
}
