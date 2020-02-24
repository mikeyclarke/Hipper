<?php
declare(strict_types=1);

namespace Hipper\Tests\Knowledgebase;

use Hipper\Document\DocumentModel;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Knowledgebase\KnowledgebaseRouteInserter;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteUpdater;
use Hipper\Topic\TopicModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class KnowledgebaseRouteTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $idGenerator;
    private $knowledgebaseRouteInserter;
    private $knowledgebaseRouteUpdater;
    private $knowledgebaseRoute;

    public function setUp(): void
    {
        $this->idGenerator = m::mock(IdGenerator::class);
        $this->knowledgebaseRouteInserter = m::mock(KnowledgebaseRouteInserter::class);
        $this->knowledgebaseRouteUpdater = m::mock(KnowledgebaseRouteUpdater::class);

        $this->knowledgebaseRoute = new KnowledgebaseRoute(
            $this->idGenerator,
            $this->knowledgebaseRouteInserter,
            $this->knowledgebaseRouteUpdater
        );
    }

    /**
     * @test
     */
    public function create()
    {
        $urlId = 'c183c427';
        $route = 'route';
        $documentId = 'document-uuid';
        $knowledgebaseId = 'knowledgebase-uuid';
        $organizationId = 'organization-uuid';

        $model = new DocumentModel;
        $model->setUrlId($urlId);
        $model->setId($documentId);
        $model->setKnowledgebaseId($knowledgebaseId);
        $model->setOrganizationId($organizationId);

        $isCanonical = true;
        $isNewDocument = true;

        $routeId = 'route-uuid';
        $routeRow = ['route-row'];

        $this->createIdGeneratorExpectation($routeId);
        $this->createKnowledgebaseRouteInserterExpectation(
            [$routeId, $urlId, $route, 'document', $organizationId, $knowledgebaseId, null, $documentId, $isCanonical],
            $routeRow
        );

        $result = $this->knowledgebaseRoute->create(
            $model,
            $route,
            $isCanonical,
            $isNewDocument
        );
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result);
    }

    /**
     * @test
     */
    public function createUpdatesPreviousCurrentRoute()
    {
        $urlId = 'c183c427';
        $route = 'route';
        $documentId = 'document-uuid';
        $knowledgebaseId = 'knowledgebase-uuid';
        $organizationId = 'organization-uuid';

        $model = new DocumentModel;
        $model->setUrlId($urlId);
        $model->setId($documentId);
        $model->setKnowledgebaseId($knowledgebaseId);
        $model->setOrganizationId($organizationId);

        $isCanonical = true;
        $isNewDocument = false;

        $routeId = 'route-uuid';
        $routeRow = [
            'id' => $routeId,
            'url_id' => $urlId,
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $organizationId,
        ];

        $this->createIdGeneratorExpectation($routeId);
        $this->createKnowledgebaseRouteInserterExpectation(
            [$routeId, $urlId, $route, 'document', $organizationId, $knowledgebaseId, null, $documentId, $isCanonical],
            $routeRow
        );
        $this->createKnowledgebaseRouteUpdaterExpectation([$routeId, $urlId, $knowledgebaseId, $organizationId]);

        $result = $this->knowledgebaseRoute->create(
            $model,
            $route,
            $isCanonical,
            $isNewDocument
        );
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result);
    }

    /**
     * @test
     */
    public function createForTopic()
    {
        $urlId = 'c183c427';
        $route = 'route';
        $topicId = 'topic-uuid';
        $knowledgebaseId = 'knowledgebase-uuid';
        $organizationId = 'organization-uuid';

        $model = new TopicModel;
        $model->setUrlId($urlId);
        $model->setId($topicId);
        $model->setKnowledgebaseId($knowledgebaseId);
        $model->setOrganizationId($organizationId);

        $isCanonical = true;
        $isNewContent = true;

        $routeId = 'route-uuid';
        $routeRow = ['route-row'];

        $this->createIdGeneratorExpectation($routeId);
        $this->createKnowledgebaseRouteInserterExpectation(
            [$routeId, $urlId, $route, 'topic', $organizationId, $knowledgebaseId, $topicId, null, $isCanonical],
            $routeRow
        );

        $result = $this->knowledgebaseRoute->create(
            $model,
            $route,
            $isCanonical,
            $isNewContent
        );
        $this->assertInstanceOf(KnowledgebaseRouteModel::class, $result);
    }

    private function createKnowledgebaseRouteUpdaterExpectation($args)
    {
        $this->knowledgebaseRouteUpdater
            ->shouldReceive('updatePreviousCanonicalRoutes')
            ->once()
            ->with(...$args);
    }

    private function createKnowledgebaseRouteInserterExpectation($args, $result)
    {
        $this->knowledgebaseRouteInserter
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
