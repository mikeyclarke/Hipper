<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization;

use Hipper\Project\ProjectSearch;
use Hipper\Security\UntrustedInternalUriRedirector;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class OrganizationProjectsSearchController
{
    private const MORE_RESULTS_ROUTE_NAME = 'api.app.organization.projects.search';

    private ProjectSearch $projectSearch;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;
    private UntrustedInternalUriRedirector $untrustedInternalUriRedirector;
    private UrlGeneratorInterface $router;

    public function __construct(
        ProjectSearch $projectSearch,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig,
        UntrustedInternalUriRedirector $untrustedInternalUriRedirector,
        UrlGeneratorInterface $router
    ) {
        $this->projectSearch = $projectSearch;
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
        $timeZone = $this->timeZoneFromRequest->get($request);
        $moreResultsEndpoint = $this->router->generate(
            self::MORE_RESULTS_ROUTE_NAME,
            [
                'subdomain' => $organization->getSubdomain(),
            ]
        );
        $searchResults = [];
        $moreResults = false;

        if (!empty($searchQuery)) {
            list($searchResults, $moreResults) = $this->projectSearch->search(
                $searchQuery,
                $timeZone,
                $organization,
                $numberOfPages
            );
        }

        $context = [
            'active_filter' => 'projects',
            'back_link' => $this->untrustedInternalUriRedirector->generateUri($returnTo, '/'),
            'html_title' => sprintf('Search “%s” – Projects', $searchQuery),
            'more_results' => $moreResults,
            'more_results_route' => $moreResultsEndpoint,
            'search_query' => $searchQuery,
            'search_results' => $searchResults,
        ];

        return new Response(
            $this->twig->render('organization/organization_search.twig', $context)
        );
    }
}
