<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseRouteUrlGenerator
{
    const EDIT_ORGANIZATION_DOC_ROUTE_NAME = 'front_end.app.organization.doc.edit';
    const EDIT_TEAM_DOC_ROUTE_NAME = 'front_end.app.team.doc.edit';
    const EDIT_PROJECT_DOC_ROUTE_NAME = 'front_end.app.project.doc.edit';
    const EXPORT_ORGANIZATION_DOC_ROUTE_NAME = 'front_end.app.organization.doc.export';
    const EXPORT_TEAM_DOC_ROUTE_NAME = 'front_end.app.team.doc.export';
    const EXPORT_PROJECT_DOC_ROUTE_NAME = 'front_end.app.project.doc.export';
    const SHOW_ORGANIZATION_DOC_ROUTE_NAME = 'front_end.app.organization.doc.show';
    const SHOW_TEAM_DOC_ROUTE_NAME = 'front_end.app.team.doc.show';
    const SHOW_PROJECT_DOC_ROUTE_NAME = 'front_end.app.project.doc.show';

    private $router;

    public function __construct(
        UrlGeneratorInterface $router
    ) {
        $this->router = $router;
    }

    public function generate(
        OrganizationModel $organization,
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        KnowledgebaseRouteModel $route,
        string $method = 'show',
        array $additionalRouteParams = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        if (!in_array($method, ['show', 'edit', 'export'])) {
            throw new \Exception('Unsupported route method');
        }

        $routeName = null;
        $routeParams = array_merge($additionalRouteParams, [
            'path' => $route->toUrlSegment(),
            'subdomain' => $organization->getSubdomain(),
        ]);

        if ($knowledgebaseOwner instanceof TeamModel) {
            switch ($method) {
                case 'edit':
                    $routeName = self::EDIT_TEAM_DOC_ROUTE_NAME;
                    break;
                case 'export':
                    $routeName = self::EXPORT_TEAM_DOC_ROUTE_NAME;
                    break;
                default:
                    $routeName = self::SHOW_TEAM_DOC_ROUTE_NAME;
            }
            $routeParams['team_url_slug'] = $knowledgebaseOwner->getUrlSlug();
        }

        if ($knowledgebaseOwner instanceof ProjectModel) {
            switch ($method) {
                case 'edit':
                    $routeName = self::EDIT_PROJECT_DOC_ROUTE_NAME;
                    break;
                case 'export':
                    $routeName = self::EXPORT_PROJECT_DOC_ROUTE_NAME;
                    break;
                default:
                    $routeName = self::SHOW_PROJECT_DOC_ROUTE_NAME;
            }
            $routeParams['project_url_slug'] = $knowledgebaseOwner->getUrlSlug();
        }

        if ($knowledgebaseOwner instanceof OrganizationModel) {
            switch ($method) {
                case 'edit':
                    $routeName = self::EDIT_ORGANIZATION_DOC_ROUTE_NAME;
                    break;
                case 'export':
                    $routeName = self::EXPORT_ORGANIZATION_DOC_ROUTE_NAME;
                    break;
                default:
                    $routeName = self::SHOW_ORGANIZATION_DOC_ROUTE_NAME;
            }
        }

        if (null === $routeName) {
            throw new UnsupportedKnowledgebaseEntityException;
        }

        return $this->router->generate($routeName, $routeParams, $referenceType);
    }
}
