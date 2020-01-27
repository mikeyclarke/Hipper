<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Hipper\Person\PersonSearch;
use Hipper\Security\UntrustedInternalUriRedirector;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class ProjectMembersSearchController
{
    private const MORE_RESULTS_ROUTE_NAME = 'api.app.project.members.search';

    private PersonSearch $personSearch;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;
    private UntrustedInternalUriRedirector $untrustedInternalUriRedirector;
    private UrlGeneratorInterface $router;

    public function __construct(
        PersonSearch $personSearch,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig,
        UntrustedInternalUriRedirector $untrustedInternalUriRedirector,
        UrlGeneratorInterface $router
    ) {
        $this->personSearch = $personSearch;
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
        $project = $request->attributes->get('project');
        $timeZone = $this->timeZoneFromRequest->get($request);
        $moreResultsEndpoint = $this->router->generate(
            self::MORE_RESULTS_ROUTE_NAME,
            [
                'project_id' => $project->getId(),
                'subdomain' => $organization->getSubdomain(),
            ]
        );
        $searchResults = [];
        $moreResults = false;

        if (!empty($searchQuery)) {
            list($searchResults, $moreResults) = $this->personSearch->searchProjectMembers(
                $searchQuery,
                $timeZone,
                $organization,
                $project,
                $numberOfPages
            );
        }

        $context = [
            'active_filter' => 'people',
            'back_link' => $this->untrustedInternalUriRedirector->generateUri($returnTo, '/'),
            'html_title' => sprintf('Search “%s” – %s project members', $searchQuery, $project->getName()),
            'more_results' => $moreResults,
            'more_results_route' => $moreResultsEndpoint,
            'search_query' => $searchQuery,
            'search_results' => $searchResults,
        ];

        return new Response(
            $this->twig->render('project/project_search.twig', $context)
        );
    }
}
