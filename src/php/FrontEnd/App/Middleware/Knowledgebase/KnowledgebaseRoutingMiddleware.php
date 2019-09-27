<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Middleware\Knowledgebase;

use Hipper\Knowledgebase\Exception\NoCanonicalRouteExistsForKnowledgebaseRouteException;
use Hipper\Knowledgebase\KnowledgebaseRouteRepository;
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
        $knowledgebaseId = $this->getKnowledgebaseId($request);

        if (null === $urlId) {
            $result = $this->knowledgebaseRouteRepository->findCanonicalRouteByRoute(
                $organizationId,
                $knowledgebaseId,
                $route
            );
            if (null === $result) {
                throw new NotFoundHttpException;
            }
            $canonicalUrl = $this->generateRouteRedirectUrl($request, $result);
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
            $canonicalUrl = $this->generateRouteRedirectUrl($request, $canonicalRoute);
            return new RedirectResponse($canonicalUrl);
        }

        $request->attributes->set('knowledgebaseId', $knowledgebaseId);
        $request->attributes->set('entityType', $result['entity']);

        if ($result['entity'] === 'document') {
            $request->attributes->set('documentId', $result['document_id']);
        }

        if ($result['entity'] === 'section') {
            $request->attributes->set('sectionId', $result['section_id']);
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

    private function getKnowledgebaseId(Request $request): string
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
    }

    private function generateRouteRedirectUrl(Request $request, array $route): string
    {
        $routeName = $request->attributes->get('_route');
        $routeParameters = $request->attributes->get('_route_params');

        $routeParameters['path'] = sprintf('%s~%s', $route['route'], $route['url_id']);

        return $this->router->generate($routeName, $routeParameters);
    }
}
