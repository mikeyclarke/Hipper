<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Organization;

use Hipper\Project\ProjectSearch;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class OrganizationProjectsSearchController
{
    private ProjectSearch $projectSearch;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;
    private UrlGeneratorInterface $router;

    public function __construct(
        ProjectSearch $projectSearch,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig,
        UrlGeneratorInterface $router
    ) {
        $this->projectSearch = $projectSearch;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function getAction(Request $request): JsonResponse
    {
        $searchQuery = $request->query->get('q', '');
        $page = $request->query->getInt('page', 2);
        $organization = $request->attributes->get('organization');
        $timeZone = $this->timeZoneFromRequest->get($request);
        $searchResults = [];
        $moreResults = false;

        if (!empty($searchQuery)) {
            list($searchResults, $moreResults) = $this->projectSearch->search(
                $searchQuery,
                $timeZone,
                $organization,
                1,
                $page
            );
        }

        $context = [
            'more_results' => $moreResults,
            'search_query' => $searchQuery,
            'search_results' => $searchResults,
            'more_results_route' => $request->getPathInfo(),
        ];

        return new JsonResponse([
            'html' => $this->twig->render('_search_results_list_items.twig', $context)
        ]);
    }
}
