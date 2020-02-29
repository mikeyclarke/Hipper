<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseBreadcrumbsFormatter
{
    const LIST_ORGANIZATION_DOCS_ROUTE_NAME = 'front_end.app.organization.docs.list';
    const LIST_PROJECT_DOCS_ROUTE_NAME = 'front_end.app.project.docs.list';
    const LIST_TEAM_DOCS_ROUTE_NAME = 'front_end.app.team.docs.list';

    private $knowledgebaseUrlGenerator;
    private $router;

    public function __construct(
        KnowledgebaseRouteUrlGenerator $knowledgebaseUrlGenerator,
        UrlGeneratorInterface $router
    ) {
        $this->knowledgebaseUrlGenerator = $knowledgebaseUrlGenerator;
        $this->router = $router;
    }

    public function format(
        OrganizationModel $organization,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        array $ancestorTopics,
        string $currentEntryName
    ): array {
        $result = array_map(
            function ($topic) use ($organization, $knowledgebaseOwner) {
                return $this->formatTopicCrumb($organization, $knowledgebaseOwner, $topic);
            },
            $ancestorTopics
        );

        array_unshift($result, $this->formatKnowledgebaseOwnerCrumb($organization, $knowledgebaseOwner));
        array_push($result, ['name' => $currentEntryName]);

        return $result;
    }

    private function formatTopicCrumb(
        OrganizationModel $organization,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        array $item
    ): array {
        $route = KnowledgebaseRouteModel::createFromArray(array_intersect_key($item, array_flip(['url_id', 'route'])));

        return [
            'name' => $item['name'],
            'pathname' => $this->knowledgebaseUrlGenerator->generate($organization, $knowledgebaseOwner, $route),
        ];
    }

    private function formatKnowledgebaseOwnerCrumb(
        OrganizationModel $organization,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner
    ): array {
        $display = '';
        $routeName = null;
        $routeParams = ['subdomain' => $organization->getSubdomain()];

        if ($knowledgebaseOwner instanceof TeamModel) {
            $display = sprintf('%s team docs', $knowledgebaseOwner->getName());
            $routeName = self::LIST_TEAM_DOCS_ROUTE_NAME;
            $routeParams['team_url_id'] = $knowledgebaseOwner->getUrlId();
        }

        if ($knowledgebaseOwner instanceof ProjectModel) {
            $display = sprintf('%s project docs', $knowledgebaseOwner->getName());
            $routeName = self::LIST_PROJECT_DOCS_ROUTE_NAME;
            $routeParams['project_url_id'] = $knowledgebaseOwner->getUrlId();
        }

        if ($knowledgebaseOwner instanceof OrganizationModel) {
            $display = sprintf('%s docs', $knowledgebaseOwner->getName());
            $routeName = self::LIST_ORGANIZATION_DOCS_ROUTE_NAME;
        }

        if (null === $routeName) {
            throw new UnsupportedKnowledgebaseEntityException;
        }

        return [
            'name' => $display,
            'pathname' => $this->router->generate($routeName, $routeParams),
        ];
    }
}
