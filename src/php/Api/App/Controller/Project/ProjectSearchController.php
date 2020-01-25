<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Project;

use Hipper\Knowledgebase\KnowledgebaseSearch;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twig\Environment as Twig;

class ProjectSearchController
{
    private KnowledgebaseSearch $knowledgebaseSearch;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;

    public function __construct(
        KnowledgebaseSearch $knowledgebaseSearch,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->knowledgebaseSearch = $knowledgebaseSearch;
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
            list($searchResults, $moreResults) = $this->knowledgebaseSearch->searchWithinKnowledgebase(
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
            'show_owners' => true,
        ];

        return new JsonResponse(
            ['html' => $this->twig->render('_search_results_list_items.twig', $context)]
        );
    }
}
