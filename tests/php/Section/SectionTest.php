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
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Organization\Exception\ResourceIsForeignToOrganizationException;
use Hipper\Person\PersonModel;
use Hipper\Project\ProjectModel;
use Hipper\Section\Section;
use Hipper\Section\SectionInserter;
use Hipper\Section\SectionModel;
use Hipper\Section\SectionRepository;
use Hipper\Section\SectionUpdater;
use Hipper\Section\SectionValidator;
use Hipper\Section\UpdateSectionDescendantRoutes;
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
    private $knowledgebaseRouteRepository;
    private $sectionInserter;
    private $sectionRepository;
    private $sectionUpdater;
    private $sectionValidator;
    private $updateSectionDescendantRoutes;
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
        $this->knowledgebaseRouteRepository = m::mock(KnowledgebaseRouteRepository::class);
        $this->sectionInserter = m::mock(SectionInserter::class);
        $this->sectionRepository = m::mock(SectionRepository::class);
        $this->sectionUpdater = m::mock(SectionUpdater::class);
        $this->sectionValidator = m::mock(SectionValidator::class);
        $this->updateSectionDescendantRoutes = m::mock(UpdateSectionDescendantRoutes::class);
        $this->urlIdGenerator = m::mock(UrlIdGenerator::class);
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);

        $this->section = new Section(
            $this->connection,
            $this->idGenerator,
            $this->knowledgebaseOwner,
            $this->knowledgebaseRepository,
            $this->knowledgebaseRoute,
            $this->knowledgebaseRouteRepository,
            $this->sectionInserter,
            $this->sectionRepository,
            $this->sectionUpdater,
            $this->sectionValidator,
            $this->updateSectionDescendantRoutes,
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
            null,
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
        $this->createSectionValidatorExpectation([$parameters, m::type(KnowledgebaseModel::class), null, true]);
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

    /**
     * @test
     */
    public function createInParentSection()
    {
        $person = new PersonModel;
        $person->setOrganizationId('org-uuid');
        $parameters = [
            'name' => 'My section',
            'description' => 'My description',
            'knowledgebase_id' => 'kb-uuid',
            'parent_section_id' => 'parent-section-uuid',
        ];

        $knowledgebaseResult = [
            'id' => $parameters['knowledgebase_id'],
        ];
        $parentSectionResult = [
            'id' => $parameters['parent_section_id'],
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
            $parameters['parent_section_id'],
        ];
        $sectionArray = [
            'id' => $sectionId,
            'url_slug' => $sectionUrlSlug,
            'url_id' => $sectionUrlId,
        ];
        $parentSectionRouteResult = ['route' => 'i/have/nested'];
        $knowledgebaseRouteModel = new KnowledgebaseRouteModel;
        $knowledgebaseOwnerModel = new ProjectModel;

        $this->createKnowledgebaseRepositoryExpectation(
            [$parameters['knowledgebase_id'], 'org-uuid'],
            $knowledgebaseResult
        );
        $this->createSectionRepositoryExpectation(
            [$parameters['parent_section_id'], $parameters['knowledgebase_id'], 'org-uuid'],
            $parentSectionResult
        );
        $this->createSectionValidatorExpectation(
            [$parameters, m::type(KnowledgebaseModel::class), m::type(SectionModel::class), true]
        );
        $this->createIdGeneratorExpectation($sectionId);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $sectionUrlSlug);
        $this->createUrlIdGeneratorExpectation($sectionUrlId);
        $this->createConnectionBeginTransactionExpectation();
        $this->createSectionInserterExpectation($sectionInserterArgs, $sectionArray);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            ['org-uuid', $parameters['knowledgebase_id'], $parameters['parent_section_id']],
            $parentSectionRouteResult
        );
        $this->createKnowledgebaseRouteExpectation(
            [m::type(SectionModel::class), $parentSectionRouteResult['route'] . '/' . $sectionUrlSlug, true, true],
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

    /**
     * @test
     */
    public function updateName()
    {
        $sectionId = 'section-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $parentSectionId = 'parent-section-uuid';
        $name = 'Bar';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $sectionModel = SectionModel::createFromArray([
            'id' => $sectionId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'url_slug' => 'foo',
            'parent_section_id' => $parentSectionId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'name' => $name,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $sectionUrlSlug = 'bar';
        $parentSectionResult = [
            'id' => $parentSectionId,
        ];
        $sectionUpdateResult = [
            'name' => $name,
            'url_slug' => $sectionUrlSlug,
        ];
        $parentSectionRouteResult = ['route' => 'parent-section'];
        $newRoute = sprintf('%s/%s', $parentSectionRouteResult['route'], $sectionUrlSlug);
        $routeModel = KnowledgebaseRouteModel::createFromArray([
            'route' => $newRoute,
        ]);

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createSectionValidatorExpectation([$parameters, null, null]);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $sectionUrlSlug);
        $this->createSectionRepositoryExpectation(
            [$parentSectionId, $knowledgebaseId, $organizationId],
            $parentSectionResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createSectionUpdaterExpectation(
            [$sectionModel->getId(), ['name' => $name, 'url_slug' => $sectionUrlSlug]],
            $sectionUpdateResult
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $parentSectionId],
            $parentSectionRouteResult
        );
        $this->createKnowledgebaseRouteExpectation(
            [$sectionModel, $newRoute, true],
            $routeModel
        );
        $this->createConnectionCommitExpectation();
        $this->createUpdateSectionDescendantRoutesExpectation(
            [$sectionModel, $routeModel]
        );

        $result = $this->section->update($person, $sectionModel, $parameters);
        $this->assertEquals($name, $sectionModel->getName());
        $this->assertEquals($sectionUrlSlug, $sectionModel->getUrlSlug());
        $this->assertIsArray($result);
        $this->assertEquals($sectionModel, $result[0]);
        $this->assertEquals($routeModel, $result[1]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[2]);
    }

    /**
     * @test
     */
    public function newRouteIsNotGeneratedIfUpdatedNameResultsInIdenticalUrlSlug()
    {
        $sectionId = 'section-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $parentSectionId = 'parent-section-uuid';
        $name = 'FOO';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $sectionModel = SectionModel::createFromArray([
            'id' => $sectionId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'url_slug' => 'foo',
            'parent_section_id' => $parentSectionId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'name' => $name,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $sectionUrlSlug = 'foo';
        $sectionRouteResult = ['route' => 'parent-section/foo'];
        $sectionUpdateResult = [
            'name' => $name,
        ];

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createSectionValidatorExpectation([$parameters, null, null]);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $sectionUrlSlug);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $sectionId],
            $sectionRouteResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createSectionUpdaterExpectation(
            [$sectionModel->getId(), ['name' => $name]],
            $sectionUpdateResult
        );
        $this->createConnectionCommitExpectation();

        $result = $this->section->update($person, $sectionModel, $parameters);
        $this->assertEquals($name, $sectionModel->getName());
        $this->assertEquals($sectionUrlSlug, $sectionModel->getUrlSlug());
        $this->assertIsArray($result);
        $this->assertEquals($sectionModel, $result[0]);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[2]);
    }

    /**
     * @test
     */
    public function moveToNewParentSection()
    {
        $sectionId = 'section-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $parentSectionId = 'parent-section-uuid';
        $newParentSectionId = 'new-parent-uuid';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $sectionModel = SectionModel::createFromArray([
            'id' => $sectionId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'url_slug' => 'foo',
            'parent_section_id' => $parentSectionId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'parent_section_id' => $newParentSectionId,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $parentSectionResult = [
            'id' => $newParentSectionId,
        ];
        $sectionUpdateResult = [
            'parent_section_id' => $newParentSectionId,
        ];
        $parentSectionRouteResult = ['route' => 'new-parent-section'];
        $newRoute = sprintf('%s/%s', $parentSectionRouteResult['route'], $sectionModel->getUrlSlug());
        $routeModel = KnowledgebaseRouteModel::createFromArray([
            'route' => $newRoute,
        ]);

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createSectionRepositoryExpectation(
            [$newParentSectionId, $knowledgebaseId, $organizationId],
            $parentSectionResult
        );
        $this->createSectionValidatorExpectation([$parameters, null, m::type(SectionModel::class)]);
        $this->createConnectionBeginTransactionExpectation();
        $this->createSectionUpdaterExpectation(
            [$sectionModel->getId(), ['parent_section_id' => $newParentSectionId]],
            $sectionUpdateResult
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $newParentSectionId],
            $parentSectionRouteResult
        );
        $this->createKnowledgebaseRouteExpectation(
            [$sectionModel, $newRoute, true],
            $routeModel
        );
        $this->createConnectionCommitExpectation();
        $this->createUpdateSectionDescendantRoutesExpectation(
            [$sectionModel, $routeModel]
        );

        $result = $this->section->update($person, $sectionModel, $parameters);
        $this->assertEquals($newParentSectionId, $sectionModel->getParentSectionId());
        $this->assertIsArray($result);
        $this->assertEquals($sectionModel, $result[0]);
        $this->assertEquals($routeModel, $result[1]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[2]);
    }

    /**
     * @test
     */
    public function updateNameWhilstMovingToNewParentSection()
    {
        $sectionId = 'section-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $parentSectionId = 'parent-section-uuid';
        $name = 'Bar';
        $newParentSectionId = 'new-parent-uuid';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $sectionModel = SectionModel::createFromArray([
            'id' => $sectionId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'url_slug' => 'foo',
            'parent_section_id' => $parentSectionId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'name' => $name,
            'parent_section_id' => $newParentSectionId,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $parentSectionResult = [
            'id' => $newParentSectionId,
        ];
        $sectionUrlSlug = 'bar';
        $sectionUpdateResult = [
            'name' => $name,
            'parent_section_id' => $newParentSectionId,
            'url_slug' => $sectionUrlSlug,
        ];
        $parentSectionRouteResult = ['route' => 'new-parent-section'];
        $newRoute = sprintf('%s/%s', $parentSectionRouteResult['route'], $sectionUrlSlug);
        $routeModel = KnowledgebaseRouteModel::createFromArray([
            'route' => $newRoute,
        ]);

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createSectionRepositoryExpectation(
            [$newParentSectionId, $knowledgebaseId, $organizationId],
            $parentSectionResult
        );
        $this->createSectionValidatorExpectation([$parameters, null, m::type(SectionModel::class)]);
        $this->createUrlSlugGeneratorExpectation([$parameters['name']], $sectionUrlSlug);
        $this->createConnectionBeginTransactionExpectation();
        $this->createSectionUpdaterExpectation(
            [
                $sectionModel->getId(),
                ['name' => $name, 'url_slug' => $sectionUrlSlug, 'parent_section_id' => $newParentSectionId]
            ],
            $sectionUpdateResult
        );
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $newParentSectionId],
            $parentSectionRouteResult
        );
        $this->createKnowledgebaseRouteExpectation(
            [$sectionModel, $newRoute, true],
            $routeModel
        );
        $this->createConnectionCommitExpectation();
        $this->createUpdateSectionDescendantRoutesExpectation(
            [$sectionModel, $routeModel]
        );

        $result = $this->section->update($person, $sectionModel, $parameters);
        $this->assertEquals($newParentSectionId, $sectionModel->getParentSectionId());
        $this->assertEquals($name, $sectionModel->getName());
        $this->assertEquals($sectionUrlSlug, $sectionModel->getUrlSlug());
        $this->assertIsArray($result);
        $this->assertEquals($sectionModel, $result[0]);
        $this->assertEquals($routeModel, $result[1]);
        $this->assertEquals($knowledgebaseOwnerModel, $result[2]);
    }

    /**
     * @test
     */
    public function updateDescription()
    {
        $sectionId = 'section-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $description = 'I changed my description';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $sectionModel = SectionModel::createFromArray([
            'id' => $sectionId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'description' => $description,
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $sectionRouteResult = ['route' => 'foo'];
        $sectionUpdateResult = [
            'description' => $description,
        ];

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createSectionValidatorExpectation([$parameters, null, null]);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $sectionId],
            $sectionRouteResult
        );
        $this->createConnectionBeginTransactionExpectation();
        $this->createSectionUpdaterExpectation(
            [$sectionModel->getId(), ['description' => $description]],
            $sectionUpdateResult
        );
        $this->createConnectionCommitExpectation();

        $result = $this->section->update($person, $sectionModel, $parameters);
        $this->assertEquals($description, $sectionModel->getDescription());
        $this->assertIsArray($result);
        $this->assertEquals($sectionModel, $result[0]);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertInstanceOf(KnowledgebaseOwnerModelInterface::class, $result[2]);
    }

    /**
     * @test
     */
    public function updateWithNothingNew()
    {
        $sectionId = 'section-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $sectionModel = SectionModel::createFromArray([
            'id' => $sectionId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $parameters = [
            'name' => 'Foo',
            'description' => 'This is my description',
        ];

        $knowledgebaseResult = [
            'id' => $knowledgebaseId,
        ];
        $knowledgebaseOwnerModel = new ProjectModel;
        $sectionRouteResult = ['route' => 'foo'];

        $this->createKnowledgebaseRepositoryExpectation([$knowledgebaseId, $organizationId], $knowledgebaseResult);
        $this->createKnowledgebaseOwnerExpectation([m::type(KnowledgebaseModel::class)], $knowledgebaseOwnerModel);
        $this->createSectionValidatorExpectation([$parameters, null, null]);
        $this->createKnowledgebaseRouteRepositoryExpectation(
            [$organizationId, $knowledgebaseId, $sectionId],
            $sectionRouteResult
        );

        $result = $this->section->update($person, $sectionModel, $parameters);
        $this->assertIsArray($result);
        $this->assertEquals($sectionModel, $result[0]);
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result[1]);
        $this->assertInstanceOf(KnowledgebaseOwnerModelInterface::class, $result[2]);
    }

    /**
     * @test
     */
    public function updateSectionOutsideCurrentUsersOrganization()
    {
        $sectionId = 'section-uuid';
        $organizationId = 'org-uuid';
        $knowledgebaseId = 'kb-uuid';
        $foreignOrganizationId = 'foreign-org-uuid';

        $person = PersonModel::createFromArray([
            'organization_id' => $organizationId,
        ]);
        $sectionModel = SectionModel::createFromArray([
            'id' => $sectionId,
            'name' => 'Foo',
            'description' => 'This is my description',
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $foreignOrganizationId,
        ]);
        $parameters = [
            'name' => 'Foo',
            'description' => 'This is my description',
        ];

        $this->expectException(ResourceIsForeignToOrganizationException::class);

        $this->section->update($person, $sectionModel, $parameters);
    }

    private function createUpdateSectionDescendantRoutesExpectation($args)
    {
        $this->updateSectionDescendantRoutes
            ->shouldReceive('update')
            ->once()
            ->with(...$args);
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

    private function createKnowledgebaseRouteRepositoryExpectation($args, $result)
    {
        $this->knowledgebaseRouteRepository
            ->shouldReceive('findCanonicalRouteForSection')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSectionUpdaterExpectation($args, $result)
    {
        $this->sectionUpdater
            ->shouldReceive('update')
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

    private function createSectionRepositoryExpectation($args, $result)
    {
        $this->sectionRepository
            ->shouldReceive('findByIdInKnowledgebase')
            ->once()
            ->with(...$args)
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
