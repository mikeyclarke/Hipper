<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Team;

use Hipper\Knowledgebase\KnowledgebaseSearch;
use Hipper\Security\UntrustedInternalUriRedirector;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class TeamSearchController
{
    private const MORE_RESULTS_ROUTE_NAME = 'api.app.team.search';

    private KnowledgebaseSearch $knowledgebaseSearch;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;
    private UntrustedInternalUriRedirector $untrustedInternalUriRedirector;
    private UrlGeneratorInterface $router;

    public function __construct(
        KnowledgebaseSearch $knowledgebaseSearch,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig,
        UntrustedInternalUriRedirector $untrustedInternalUriRedirector,
        UrlGeneratorInterface $router
    ) {
        $this->knowledgebaseSearch = $knowledgebaseSearch;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
        $this->untrustedInternalUriRedirector = $untrustedInternalUriRedirector;
        $this->router = $router;
    }

    public function getAction(Request $request): Response
    {
        $searchQuery = $request->query->get('q', '');
        $returnTo = $request->query->get('return_to');
        $numberOfPages = $request->query->getInt('p', 1);
        $organization = $request->attributes->get('organization');
        $team = $request->attributes->get('team');
        $timeZone = $this->timeZoneFromRequest->get($request);
        $moreResultsEndpoint = $this->router->generate(
            self::MORE_RESULTS_ROUTE_NAME,
            [
                'team_url_id' => $team->getUrlId(),
                'subdomain' => $organization->getSubdomain(),
            ]
        );
        $searchResults = [];
        $moreResults = false;

        if (!empty($searchQuery)) {
            list($searchResults, $moreResults) = $this->knowledgebaseSearch->searchWithinKnowledgebase(
                $searchQuery,
                $timeZone,
                $organization,
                $team,
                $numberOfPages
            );
        }

        $context = [
            'back_link' => $this->untrustedInternalUriRedirector->generateUri($returnTo, '/'),
            'html_title' => sprintf('Search “%s” – %s team', $searchQuery, $team->getName()),
            'more_results' => $moreResults,
            'more_results_route' => $moreResultsEndpoint,
            'search_query' => $searchQuery,
            'search_results' => $searchResults,
        ];

        return new Response(
            $this->twig->render('team/team_search.twig', $context)
        );
    }
}
