<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\App\Controller\Document;

use Hipper\Api\App\Controller\Document\UpdateDocumentController;
use Hipper\Document\Document;
use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRepository;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use Hipper\Team\TeamModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateDocumentControllerTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $document;
    private $documentRepository;
    private $knowledgebaseRouteUrlGenerator;
    private $controller;
    private $person;
    private $organization;

    public function setUp(): void
    {
        $this->document = m::mock(Document::class);
        $this->documentRepository = m::mock(DocumentRepository::class);
        $this->knowledgebaseRouteUrlGenerator = m::mock(KnowledgebaseRouteUrlGenerator::class);

        $this->controller = new UpdateDocumentController(
            $this->document,
            $this->documentRepository,
            $this->knowledgebaseRouteUrlGenerator
        );

        $this->person = new PersonModel;
        $this->person->setOrganizationId('org-uuid');

        $this->organization = new OrganizationModel;
    }

    /**
     * @test
     */
    public function postAction()
    {
        $request = new Request(
            [],
            ['name' => 'Foo'],
            [
                'document_id' => 'doc-uuid',
                'current_user' => $this->person,
                'organization' => $this->organization,
            ]
        );

        $documentResult = ['id' => 'doc-uuid'];
        $knowledgebaseOwner = new TeamModel;
        $route = new KnowledgebaseRouteModel;
        $url = 'https://acme.usehipper.com/teams/engineering/docs/foo/bar~bb239b51';

        $this->createDocumentRepositoryExpectation(
            [$request->attributes->get('document_id'), $this->person->getOrganizationId()],
            $documentResult
        );
        $this->createDocumentUpdateExpectation(
            [$this->person, m::type(DocumentModel::class), $request->request->all()],
            [$route, $knowledgebaseOwner]
        );
        $this->createKnowledgebaseRouteUrlGeneratorExpectation(
            [$this->organization, $knowledgebaseOwner, $route],
            $url
        );

        $result = $this->controller->postAction($request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals($url, json_decode($result->getContent(), true)['doc_url']);
    }

    /**
     * @test
     */
    public function documentNotFound()
    {
        $request = new Request(
            [],
            ['name' => 'Foo'],
            [
                'document_id' => 'doc-uuid',
                'current_user' => $this->person,
                'organization' => $this->organization,
            ]
        );

        $this->createDocumentRepositoryExpectation(
            [$request->attributes->get('document_id'), $this->person->getOrganizationId()],
            null
        );

        $this->expectException(NotFoundHttpException::class);

        $this->controller->postAction($request);
    }

    private function createKnowledgebaseRouteUrlGeneratorExpectation($args, $result)
    {
        $this->knowledgebaseRouteUrlGenerator
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createDocumentUpdateExpectation($args, $result)
    {
        $this->document
            ->shouldReceive('update')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createDocumentRepositoryExpectation($args, $result)
    {
        $this->documentRepository
            ->shouldReceive('findById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
