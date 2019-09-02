<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseRouteUrlGenerator
{
    const GET_TEAM_DOC_ROUTE_NAME = 'team_doc.get';
    const GET_PROJECT_DOC_ROUTE_NAME = 'project_doc.get';

    private $router;

    public function __construct(
        UrlGeneratorInterface $router
    ) {
        $this->router = $router;
    }

    public function generate(
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        KnowledgebaseRouteModel $route
    ): string {
        $routeName = null;
        $routeParams = [
            'path' => $route->toUrlSegment(),
        ];

        if ($knowledgebaseOwner instanceof TeamModel) {
            $routeName = self::GET_TEAM_DOC_ROUTE_NAME;
            $routeParams['team_url_id'] = $knowledgebaseOwner->getUrlId();
        }

        if ($knowledgebaseOwner instanceof ProjectModel) {
            $routeName = self::GET_PROJECT_DOC_ROUTE_NAME;
            $routeParams['project_url_id'] = $knowledgebaseOwner->getUrlId();
        }

        if (null === $routeName) {
            throw new UnsupportedKnowledgebaseEntityException;
        }

        return $this->router->generate($routeName, $routeParams);
    }
}
