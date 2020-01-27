<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Project;

use Hipper\Person\PersonSearch;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment as Twig;

class ProjectMembersSearchController
{
    private PersonSearch $personSearch;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;

    public function __construct(
        PersonSearch $personSearch,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->personSearch = $personSearch;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): JsonResponse
    {
        $searchQuery = $request->query->get('q', '');
        $page = $request->query->getInt('page', 2);
        $organization = $request->attributes->get('organization');
        $project = $request->attributes->get('project');
        $timeZone = $this->timeZoneFromRequest->get($request);
        $searchResults = [];
        $moreResults = false;

        if (!empty($searchQuery)) {
            list($searchResults, $moreResults) = $this->personSearch->searchProjectMembers(
                $searchQuery,
                $timeZone,
                $organization,
                $project,
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

        return new JsonResponse(
            ['html' => $this->twig->render('_search_results_list_items.twig', $context)]
        );
    }
}
