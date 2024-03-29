<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Knowledgebase;

use Hipper\Knowledgebase\Exception\NoCanonicalRouteExistsForKnowledgebaseRouteException;
use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
use Hipper\Organization\OrganizationModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class KnowledgebaseRoutingMiddleware
{
    private $knowledgebaseRouteRepository;
    private $router;

    public function __construct(
        KnowledgebaseRouteRepository $knowledgebaseRouteRepository,
        UrlGeneratorInterface $router
    ) {
        $this->knowledgebaseRouteRepository = $knowledgebaseRouteRepository;
        $this->router = $router;
    }

    public function before(Request $request)
    {
        $path = $request->attributes->get('path');
        list($route, $urlId) = $this->getRouteAndUrlId($path);

        $organization = $request->attributes->get('organization');
        $organizationId = $organization->getId();
        $knowledgebaseId = $this->getKnowledgebaseId($request, $organization);

        if (null === $urlId) {
            $result = $this->knowledgebaseRouteRepository->findCanonicalRouteByRoute(
                $organizationId,
                $knowledgebaseId,
                $route
            );
            if (null === $result) {
                throw new NotFoundHttpException;
            }
            $canonicalUrl = $this->generateRouteRedirectUrl($request, $organization, $result);
            return new RedirectResponse($canonicalUrl);
        }

        $result = $this->knowledgebaseRouteRepository->findRouteWithRouteAndUrlId(
            $organizationId,
            $knowledgebaseId,
            $route,
            $urlId
        );
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        if (false === $result['is_canonical']) {
            $canonicalRoute = $this->knowledgebaseRouteRepository->findCanonicalRouteByUrlId(
                $organizationId,
                $knowledgebaseId,
                $result['url_id']
            );
            if (null === $canonicalRoute) {
                throw new NoCanonicalRouteExistsForKnowledgebaseRouteException;
            }
            $canonicalUrl = $this->generateRouteRedirectUrl($request, $organization, $canonicalRoute);
            return new RedirectResponse($canonicalUrl);
        }

        $request->attributes->set('knowledgebase_id', $knowledgebaseId);
        $request->attributes->set('knowledgebase_route', KnowledgebaseRouteModel::createFromArray($result));
        $request->attributes->set('entity_type', $result['entity']);

        if ($result['entity'] === 'document') {
            $request->attributes->set('document_id', $result['document_id']);
        }

        if ($result['entity'] === 'topic') {
            $request->attributes->set('topic_id', $result['topic_id']);
        }
    }

    private function getRouteAndUrlId(string $path): array
    {
        preg_match('/~([a-zA-Z0-9]{8,8})$/', $path, $matches);
        if (empty($matches) || !isset($matches[1])) {
            return [$path, null];
        }

        return [mb_substr($path, 0, -9), $matches[1]];
    }

    private function getKnowledgebaseId(Request $request, OrganizationModel $organization): string
    {
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');
        if ($knowledgebaseType === 'team') {
            $team = $request->attributes->get('team');
            return $team->getKnowledgebaseId();
        }

        if ($knowledgebaseType === 'project') {
            $project = $request->attributes->get('project');
            return $project->getKnowledgebaseId();
        }

        if ($knowledgebaseType === 'organization') {
            return $organization->getKnowledgebaseId();
        }

        throw new UnsupportedKnowledgebaseEntityException;
    }

    private function generateRouteRedirectUrl(Request $request, OrganizationModel $organization, array $route): string
    {
        $routeName = $request->attributes->get('_route');
        $routeParameters = $request->query->all();
        $routeParameters = array_merge($routeParameters, $request->attributes->get('_route_params'));

        $routeParameters['path'] = sprintf('%s~%s', $route['route'], $route['url_id']);
        $routeParameters['subdomain'] = $organization->getSubdomain();

        return $this->router->generate($routeName, $routeParameters);
    }
}
