<?php
declare(strict_types=1);

namespace Hipper\Tests\Api\App\Controller\Topic;

use Hipper\Api\App\Controller\Topic\UpdateTopicController;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Organization\OrganizationModel;
use Hipper\Person\PersonModel;
use Hipper\Team\TeamModel;
use Hipper\Topic\TopicUpdater;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class UpdateTopicControllerTest extends TestCase
{
    private $knowledgebaseBreadcrumbs;
    private $knowledgebaseRouteUrlGenerator;
    private $topicRepository;
    private $topicUpdater;
    private $twig;
    private $router;
    private $updateTopicController;
    private $currentUser;
    private $organization;

    public function setUp(): void
    {
        $this->knowledgebaseBreadcrumbs = m::mock(KnowledgebaseBreadcrumbs::class);
        $this->knowledgebaseRouteUrlGenerator = m::mock(KnowledgebaseRouteUrlGenerator::class);
        $this->topicRepository = m::mock(TopicRepository::class);
        $this->topicUpdater = m::mock(TopicUpdater::class);
        $this->twig = m::mock(Twig::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->updateTopicController = new UpdateTopicController(
            $this->knowledgebaseBreadcrumbs,
            $this->knowledgebaseRouteUrlGenerator,
            $this->topicRepository,
            $this->topicUpdater,
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
                'topic_id' => 'topic-uuid',
                'current_user' => $this->currentUser,
                'organization' => $this->organization,
            ]
        );

        $topicResult = ['id' => 'topic-uuid'];
        $knowledgebaseId = 'kb-uuid';
        $topicModel = TopicModel::createFromArray([
            'id' => 'topic-uuid',
            'name' => 'Topic',
            'knowledgebase_id' => $knowledgebaseId,
            'organization_id' => $this->organization->getId(),
        ]);
        $knowledgebaseOwner = TeamModel::createFromArray([
            'url_slug' => 'foo',
        ]);
        $route = new KnowledgebaseRouteModel;
        $breadcrumbsResult = ['breadcrumbs'];
        $url = 'https://acme.usehipper.com/teams/engineering/docs/foo~lb23tb50';
        $twigResult = '<div>html</div>';

        $this->createTopicRepositoryExpectation(
            [$request->attributes->get('topic_id'), $this->organization->getId()],
            $topicResult
        );
        $this->createTopicUpdaterExpectation(
            [$this->currentUser, m::type(TopicModel::class), $request->request->all()],
            [$topicModel, $route, $knowledgebaseOwner]
        );
        $this->createKnowledgebaseBreadcrumbsExpectation(
            [$this->organization, $knowledgebaseOwner, $topicModel->getName(), $topicModel->getParentTopicId()],
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
            ['front_end.app.team.topic.create', m::type('array')],
            '/create-topic'
        );
        $this->createTwigExpectation(
            ['topic/_topic_header.twig', m::type('array')],
            $twigResult
        );

        $result = $this->updateTopicController->postAction($request);
        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals($url, json_decode($result->getContent(), true)['topic_url']);
        $this->assertEquals($twigResult, json_decode($result->getContent(), true)['header_html']);
    }

    /**
     * @test
     */
    public function topicNotFound()
    {
        $request = new Request(
            [],
            ['name' => 'Foo'],
            [
                'topic_id' => 'topic-uuid',
                'current_user' => $this->currentUser,
                'organization' => $this->organization,
            ]
        );

        $this->createTopicRepositoryExpectation(
            [$request->attributes->get('topic_id'), $this->organization->getId()],
            null
        );

        $this->expectException(NotFoundHttpException::class);

        $this->updateTopicController->postAction($request);
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

    private function createTopicUpdaterExpectation($args, $result)
    {
        $this->topicUpdater
            ->shouldReceive('update')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createTopicRepositoryExpectation($args, $result)
    {
        $this->topicRepository
            ->shouldReceive('findById')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
