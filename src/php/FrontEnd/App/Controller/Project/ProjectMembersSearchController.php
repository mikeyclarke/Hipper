<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Hipper\Person\PersonSearch;
use Hipper\Security\UntrustedInternalUriRedirector;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class ProjectMembersSearchController
{
    private PersonSearch $personSearch;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;
    private UntrustedInternalUriRedirector $untrustedInternalUriRedirector;

    public function __construct(
        PersonSearch $personSearch,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig,
        UntrustedInternalUriRedirector $untrustedInternalUriRedirector
    ) {
        $this->personSearch = $personSearch;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
        $this->untrustedInternalUriRedirector = $untrustedInternalUriRedirector;
    }

    public function getAction(Request $request): Response
    {
        $searchQuery = $request->query->get('q', '');
        $returnTo = $request->query->get('return_to');
        $organization = $request->attributes->get('organization');
        $project = $request->attributes->get('project');
        $timeZone = $this->timeZoneFromRequest->get($request);
        $searchResults = [];

        if (!empty($searchQuery)) {
            $searchResults = $this->personSearch->searchProjectMembers(
                $searchQuery,
                $timeZone,
                $organization,
                $project
            );
        }

        $context = [
            'active_filter' => 'people',
            'back_link' => $this->untrustedInternalUriRedirector->generateUri($returnTo, '/'),
            'html_title' => sprintf('Search “%s” – %s project members', $searchQuery, $project->getName()),
            'search_query' => $searchQuery,
            'search_results' => $searchResults,
        ];

        return new Response(
            $this->twig->render('project/project_search.twig', $context)
        );
    }
}
