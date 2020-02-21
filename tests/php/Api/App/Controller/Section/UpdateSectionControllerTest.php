<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\App\Controller\Section;

use Hipper\Api\App\Controller\Section\UpdateSectionController;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use Hipper\Section\Section;
use Hipper\Section\SectionModel;
use Hipper\Section\SectionRepository;
use Hipper\Team\TeamModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class UpdateSectionControllerTest extends TestCase
{
    private $knowledgebaseBreadcrumbs;
    private $knowledgebaseRouteUrlGenerator;
    private $section;
    private $sectionRepository;
    private $twig;
    private $router;
    private $updateSectionController;
    private $currentUser;
    private $organization;

    public function setUp(): void
    {
        $this->knowledgebaseBreadcrumbs = m::mock(KnowledgebaseBreadcrumbs::class);
        $this->knowledgebaseRouteUrlGenerator = m::mock(KnowledgebaseRouteUrlGenerator::class);
        $this->section = m::mock(Section::class);
        $this->sectionRepository = m::mock(SectionRepository::class);
        $this->twig = m::mock(Twig::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->updateSectionController = new UpdateSectionController(
            $this->knowledgebaseBreadcrumbs,
            $this->knowledgebaseRouteUrlGenerator,
            $this->section,
            $this->sectionRepository,
            $this->twig,
            $this->router
        );

        $this->currentUser = new PersonModel;
        $this->organization = OrganizationModel::createFromArray([
            'id' => 'org-uuid',
            'subdomain' => 'acme',
        ]);
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
                'section_id' => 'section-uuid',
                'current_user' => $this->currentUser,
                'organization' => $this->organization,
            ]
        );

        $sectionResult = ['id' => 'section-uuid'];
        $knowledgebaseId = 'kb-uuid';
        $sectionModel = SectionModel::createFromArray([
            'id' => 'section-uuid',
            'name' => 'Section',
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $this->organization->getId(),
        ]);
        $knowledgebaseOwner = TeamModel::createFromArray([
            'url_id' => 'foo',
        ]);
        $route = new KnowledgebaseRouteModel;
        $breadcrumbsResult = ['breadcrumbs'];
        $url = 'https://acme.usehipper.com/teams/engineering/docs/foo~lb23tb50';
        $twigResult = '<div>html</div>';

        $this->createSectionRepositoryExpectation(
            [$request->attributes->get('section_id'), $this->organization->getId()],
            $sectionResult
        );
        $this->createSectionUpdateExpectation(
            [$this->currentUser, m::type(SectionModel::class), $request->request->all()],
            [$sectionModel, $route, $knowledgebaseOwner]
        );
        $this->createKnowledgebaseBreadcrumbsExpectation(
            [$this->organization, $knowledgebaseOwner, $sectionModel->getName(), $sectionModel->getParentSectionId()],
            $breadcrumbsResult
        );
        $this->createKnowledgebaseRouteUrlGeneratorExpectation(
            [$this->organization, $knowledgebaseOwner, $route],
            $url
        );
        $this->createRouterExpectation(
            ['front_end.app.team.doc.create', m::type('array')],
            '/create-doc'
        );
        $this->createRouterExpectation(
            ['front_end.app.team.section.create', m::type('array')],
            '/create-section'
        );
        $this->createTwigExpectation(
            ['section/_section_header.twig', m::type('array')],
            $twigResult
        );

        $result = $this->updateSectionController->postAction($request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals($url, json_decode($result->getContent(), true)['section_url']);
        $this->assertEquals($twigResult, json_decode($result->getContent(), true)['header_html']);
    }

    /**
     * @test
     */
    public function sectionNotFound()
    {
        $request = new Request(
            [],
            ['name' => 'Foo'],
            [
                'section_id' => 'section-uuid',
                'current_user' => $this->currentUser,
                'organization' => $this->organization,
            ]
        );

        $this->createSectionRepositoryExpectation(
            [$request->attributes->get('section_id'), $this->organization->getId()],
            null
        );

        $this->expectException(NotFoundHttpException::class);

        $this->updateSectionController->postAction($request);
    }

    private function createTwigExpectation($args, $result)
    {
        $this->twig
            ->shouldReceive('render')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createRouterExpectation($args, $result)
    {
        $this->router
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseRouteUrlGeneratorExpectation($args, $result)
    {
        $this->knowledgebaseRouteUrlGenerator
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseBreadcrumbsExpectation($args, $result)
    {
        $this->knowledgebaseBreadcrumbs
            ->shouldReceive('get')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSectionUpdateExpectation($args, $result)
    {
        $this->section
            ->shouldReceive('update')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createSectionRepositoryExpectation($args, $result)
    {
        $this->sectionRepository
            ->shouldReceive('findById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
