<?php
declare(strict_types=1);

namespace Hipper\Tests\FrontEnd\App\Middleware\Knowledgebase;

use Hipper\FrontEnd\App\Middleware\Knowledgebase\KnowledgebaseRoutingMiddleware;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Team\TeamModel;
use Hipper\Organization\OrganizationModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseRoutingMiddlewareTest extends TestCase
{
    private $knowledgebaseRouteRepository;
    private $router;
    private $middleware;
    private $organization;
    private $team;
    private $request;

    public function setUp(): void
    {
        $this->knowledgebaseRouteRepository = m::mock(KnowledgebaseRouteRepository::class);
        $this->router = m::mock(UrlGeneratorInterface::class);

        $this->middleware = new KnowledgebaseRoutingMiddleware(
            $this->knowledgebaseRouteRepository,
            $this->router
        );

        $this->organization = new OrganizationModel;
        $this->organization->setId('org-uuid');
        $this->organization->setSubdomain('acme');

        $this->team = new TeamModel;
        $this->team->setKnowledgebaseId('kb-uuid');

        $this->request = new Request;
        $this->request->attributes->set('_route', 'route-name');
        $this->request->attributes->set('organization', $this->organization);
        $this->request->attributes->set('knowledgebase_type', 'team');
        $this->request->attributes->set('team', $this->team);
    }

    /**
     * @test
     */
    public function routeWithNoUrlIdAndNotFound404s()
    {
        $route = 'some-topic/no-document-here';

        $this->request->attributes->set('path', $route);

        $this->createKnowledgebaseRouteRepositoryFindCanonicalRouteByRouteExpectation(
            [$this->organization->getId(), $this->team->getKnowledgebaseId(), $route],
            null
        );

        $this->expectException(NotFoundHttpException::class);

        $this->middleware->before($this->request);
    }

    /**
     * @test
     */
    public function routeWithNoUrlIdShouldRedirectToCanonicalRouteWhichIncludesUrlId()
    {
        $route = 'some-topic/some-document';

        $this->request->attributes->set('path', $route);
        $this->request->attributes->set('_route_params', ['path' => $route]);

        $kbRouteResult = [
            'route' => $route,
            'url_id' => 'url-id'
        ];
        $canonicalRedirectUrl = '/team/engineering/docs/some-topic/some-document~url-id';

        $this->createKnowledgebaseRouteRepositoryFindCanonicalRouteByRouteExpectation(
            [$this->organization->getId(), $this->team->getKnowledgebaseId(), $route],
            $kbRouteResult
        );
        $this->createRouterExpectation(
            [
                $this->request->attributes->get('_route'),
                [
                    'path' => sprintf('%s~%s', $route, 'url-id'),
                    'subdomain' => $this->organization->getSubdomain(),
                ]
            ],
            $canonicalRedirectUrl
        );

        $result = $this->middleware->before($this->request);
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals($canonicalRedirectUrl, $result->getTargetUrl());
    }

    /**
     * @test
     */
    public function routeWithUrlIdNotFound404s()
    {
        $route = 'some-topic/no-document-here';
        $urlId = 'ab23n9a0';

        $this->request->attributes->set('path', sprintf('%s~%s', $route, $urlId));

        $this->createKnowledgebaseRouteRepositoryFindRouteWithRouteAndUrlId(
            [$this->organization->getId(), $this->team->getKnowledgebaseId(), $route, $urlId],
            null
        );

        $this->expectException(NotFoundHttpException::class);

        $this->middleware->before($this->request);
    }

    /**
     * @test
     */
    public function routeWithUrlIdButNotCanonicalRedirectsToCanonical()
    {
        $route = 'some-topic/i-was-called-this';
        $urlId = 'ab23n9a0';
        $path = sprintf('%s~%s', $route, $urlId);

        $this->request->attributes->set('path', $path);
        $this->request->attributes->set('_route_params', ['path' => $path]);

        $kbRouteResult = [
            'route' => $route,
            'url_id' => $urlId,
            'is_canonical' => false,
        ];
        $canonicalKbRouteResult = [
            'route' => 'some-topic/but-i-got-renamed',
            'url_id' => $urlId,
            'is_canonical' => true,
        ];
        $canonicalRedirectUrl = '/team/engineering/docs/some-topic/but-i-got-renamed~' . $urlId;

        $this->createKnowledgebaseRouteRepositoryFindRouteWithRouteAndUrlId(
            [$this->organization->getId(), $this->team->getKnowledgebaseId(), $route, $urlId],
            $kbRouteResult
        );
        $this->createKnowledgebaseRouteRepositoryFindCanonicalRouteByUrlId(
            [$this->organization->getId(), $this->team->getKnowledgebaseId(), $urlId],
            $canonicalKbRouteResult
        );
        $this->createRouterExpectation(
            [
                $this->request->attributes->get('_route'),
                [
                    'path' => sprintf('%s~%s', $canonicalKbRouteResult['route'], $urlId),
                    'subdomain' => $this->organization->getSubdomain(),
                ]
            ],
            $canonicalRedirectUrl
        );

        $result = $this->middleware->before($this->request);
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals($canonicalRedirectUrl, $result->getTargetUrl());
    }

    private function createKnowledgebaseRouteRepositoryFindCanonicalRouteByUrlId($args, $result)
    {
        $this->knowledgebaseRouteRepository
            ->shouldReceive('findCanonicalRouteByUrlId')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseRouteRepositoryFindRouteWithRouteAndUrlId($args, $result)
    {
        $this->knowledgebaseRouteRepository
            ->shouldReceive('findRouteWithRouteAndUrlId')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createKnowledgebaseRouteRepositoryFindCanonicalRouteByRouteExpectation($args, $result)
    {
        $this->knowledgebaseRouteRepository
            ->shouldReceive('findCanonicalRouteByRoute')
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
}
