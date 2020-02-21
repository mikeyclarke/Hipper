<?php
declare(strict_types=1);

namespace Hipper\Tests\Section;

use Doctrine\DBAL\Connection;
use Hipper\Document\DocumentModel;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Section\SectionModel;
use Hipper\Section\SectionRepository;
use Hipper\Section\UpdateSectionDescendantRoutes;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class UpdateSectionDescendantRoutesTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $connection;
    private $knowledgebaseRoute;
    private $sectionRepository;
    private $updateSectionDescendantRoutes;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);
        $this->knowledgebaseRoute = m::mock(KnowledgebaseRoute::class);
        $this->sectionRepository = m::mock(SectionRepository::class);

        $this->updateSectionDescendantRoutes = new UpdateSectionDescendantRoutes(
            $this->connection,
            $this->knowledgebaseRoute,
            $this->sectionRepository
        );
    }

    /**
     * @test
     */
    public function update()
    {
        $sectionId = 'section-uuid';
        $knowledgebaseId = 'kb-uuid';
        $organizationId = 'org-uuid';
        $section = SectionModel::createFromArray([
            'id' => $sectionId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ]);
        $sectionRoute = KnowledgebaseRouteModel::createFromArray([
            'route' => 'updated-top-section',
        ]);

        $sectionAndDescendantsResult = [
            [
                'id' => $sectionId,
                'url_id' => 'abc123',
                'url_slug' => 'updated-top-section',
                'parent_section_id' => null,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'section',
            ],
            [
                'id' => 'doc1-uuid',
                'url_id' => 'def456',
                'url_slug' => 'doc1',
                'parent_section_id' => $sectionId,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
            [
                'id' => 'sub-section1-uuid',
                'url_id' => 'ghi789',
                'url_slug' => 'subsection1',
                'parent_section_id' => $sectionId,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'section',
            ],
            [
                'id' => 'sub-section2-uuid',
                'url_id' => 'jkl012',
                'url_slug' => 'subsection2',
                'parent_section_id' => $sectionId,
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'section',
            ],
            [
                'id' => 'sub-section1-doc1-uuid',
                'url_id' => 'mno345',
                'url_slug' => 'doc1',
                'parent_section_id' => 'sub-section1-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
            [
                'id' => 'sub-section1-doc2-uuid',
                'url_id' => 'pqr678',
                'url_slug' => 'doc2',
                'parent_section_id' => 'sub-section1-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
            [
                'id' => 'sub-section2-doc1-uuid',
                'url_id' => 'stu901',
                'url_slug' => 'doc1',
                'parent_section_id' => 'sub-section2-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
            [
                'id' => 'sub-section2-sub-section1-uuid',
                'url_id' => 'vwx234',
                'url_slug' => 'subsection1',
                'parent_section_id' => 'sub-section2-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'section',
            ],
            [
                'id' => 'sub-section2-sub-section1-doc1-uuid',
                'url_id' => 'yza567',
                'url_slug' => 'doc1',
                'parent_section_id' => 'sub-section2-sub-section1-uuid',
                'knowledgebase_id' => $knowledgebaseId,
                'organization_id' => $organizationId,
                'type' => 'document',
            ],
        ];

        $this->createSectionRepositoryExpectation(
            [$sectionId, $knowledgebaseId, $organizationId],
            $sectionAndDescendantsResult
        );

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-section/doc1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-section/doc1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(SectionModel::class),
                'updated-top-section/subsection1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-section/subsection1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(SectionModel::class),
                'updated-top-section/subsection2',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-section/subsection2'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-section/subsection1/doc1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-section/subsection1/doc1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-section/subsection1/doc2',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-section/subsection1/doc2'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-section/subsection2/doc1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-section/subsection2/doc1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(SectionModel::class),
                'updated-top-section/subsection2/subsection1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-section/subsection2/subsection1'])
        );
        $this->createConnectionCommitExpectation();

        $this->createConnectionBeginTransactionExpectation();
        $this->createKnowledgebaseRouteExpectation(
            [
                m::type(DocumentModel::class),
                'updated-top-section/subsection2/subsection1/doc1',
                true
            ],
            KnowledgebaseRouteModel::createFromArray(['route' => 'updated-top-section/subsection2/subsection1/doc1'])
        );
        $this->createConnectionCommitExpectation();

        $this->updateSectionDescendantRoutes->update($section, $sectionRoute);
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

    private function createConnectionBeginTransactionExpectation()
    {
        $this->connection
            ->shouldReceive('beginTransaction')
            ->once();
    }

    private function createSectionRepositoryExpectation($args, $result)
    {
        $this->sectionRepository
            ->shouldReceive('getSectionAndDescendants')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
