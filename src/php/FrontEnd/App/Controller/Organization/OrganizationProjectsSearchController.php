<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization;

use Hipper\Project\ProjectSearch;
use Hipper\Security\UntrustedInternalUriRedirector;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class OrganizationProjectsSearchController
{
    private ProjectSearch $projectSearch;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;
    private UntrustedInternalUriRedirector $untrustedInternalUriRedirector;

    public function __construct(
        ProjectSearch $projectSearch,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig,
        UntrustedInternalUriRedirector $untrustedInternalUriRedirector
    ) {
        $this->projectSearch = $projectSearch;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
        $this->untrustedInternalUriRedirector = $untrustedInternalUriRedirector;
    }

    public function getAction(Request $request): Response
    {
        $searchQuery = $request->query->get('q', '');
        $returnTo = $request->query->get('return_to');
        $organization = $request->attributes->get('organization');
        $timeZone = $this->timeZoneFromRequest->get($request);
        $searchResults = [];

        if (!empty($searchQuery)) {
            $searchResults = $this->projectSearch->search($searchQuery, $timeZone, $organization);
        }

        $context = [
            'active_filter' => 'projects',
            'back_link' => $this->untrustedInternalUriRedirector->generateUri($returnTo, '/'),
            'html_title' => sprintf('Search “%s” – Projects', $searchQuery),
            'search_query' => $searchQuery,
            'search_results' => $searchResults,
        ];

        return new Response(
            $this->twig->render('organization/organization_search.twig', $context)
        );
    }
}
