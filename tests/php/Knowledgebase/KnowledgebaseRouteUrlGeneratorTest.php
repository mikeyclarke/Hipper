<?php
declare(strict_types=1);

namespace Hipper\Tests\Knowledgebase;

use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseRouteUrlGeneratorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $router;
    private $knowledgebaseRouteUrlGenerator;
    private $route;

    public function setUp(): void
    {
        $this->router = m::mock(UrlGeneratorInterface::class);
        $this->knowledgebaseRouteUrlGenerator = new KnowledgebaseRouteUrlGenerator(
            $this->router
        );

        $this->route = new KnowledgebaseRouteModel;
        $this->route->setRoute('knowledgebase-route');
        $this->route->setUrlId('abcd1234');
    }

    /**
     * @test
     */
    public function generateForRouteInTeamKnowledgebase()
    {
        $knowledgebaseOwner = new TeamModel;
        $knowledgebaseOwner->setUrlId('team-name');

        $url = '/team/team-name/docs/knowledgebase-route~abcd1234';

        $this->createRouterExpectation(
            [
                KnowledgebaseRouteUrlGenerator::GET_TEAM_DOC_ROUTE_NAME,
                ['path' => $this->route->toUrlSegment(), 'team_url_id' => $knowledgebaseOwner->getUrlId()]
            ],
            $url
        );

        $result = $this->knowledgebaseRouteUrlGenerator->generate($knowledgebaseOwner, $this->route);
        $this->assertEquals($url, $result);
    }

    /**
     * @test
     */
    public function generateForRouteInProjectKnowledgebase()
    {
        $knowledgebaseOwner = new ProjectModel;
        $knowledgebaseOwner->setUrlId('project-name');

        $url = '/project/project-name/docs/knowledgebase-route~abcd1234';

        $this->createRouterExpectation(
            [
                KnowledgebaseRouteUrlGenerator::GET_PROJECT_DOC_ROUTE_NAME,
                ['path' => $this->route->toUrlSegment(), 'project_url_id' => $knowledgebaseOwner->getUrlId()]
            ],
            $url
        );

        $result = $this->knowledgebaseRouteUrlGenerator->generate($knowledgebaseOwner, $this->route);
        $this->assertEquals($url, $result);
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
