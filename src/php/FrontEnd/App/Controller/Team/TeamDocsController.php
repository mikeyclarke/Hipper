<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Team;

use Hipper\Knowledgebase\KnowledgebaseEntries;
use Hipper\Knowledgebase\KnowledgebaseEntriesListFormatter;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class TeamDocsController
{
    private const CREATE_DOC_ROUTE_NAME = 'front_end.app.team.doc.create';
    private const CREATE_TOPIC_ROUTE_NAME = 'front_end.app.team.topic.create';

    private KnowledgebaseEntries $knowledgebaseEntries;
    private KnowledgebaseEntriesListFormatter $knowledgebaseEntriesListFormatter;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;
    private UrlGeneratorInterface $router;

    public function __construct(
        KnowledgebaseEntries $knowledgebaseEntries,
        KnowledgebaseEntriesListFormatter $knowledgebaseEntriesListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig,
        UrlGeneratorInterface $router
    ) {
        $this->knowledgebaseEntries = $knowledgebaseEntries;
        $this->knowledgebaseEntriesListFormatter = $knowledgebaseEntriesListFormatter;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $team = $request->attributes->get('team');
        $currentUserIsInTeam = $request->attributes->get('current_user_is_in_team');
        $subdomain = $organization->getSubdomain();
        $teamUrlId = $team->getUrlId();

        $createDocRoute = $this->router->generate(self::CREATE_DOC_ROUTE_NAME, [
            'team_url_id' => $teamUrlId,
            'subdomain' => $subdomain,
        ]);
        $createTopicRoute = $this->router->generate(self::CREATE_TOPIC_ROUTE_NAME, [
            'team_url_id' => $teamUrlId,
            'subdomain' => $subdomain,
            'return_to' => $request->getRequestUri(),
        ]);

        list($docs, $topics) = $this->knowledgebaseEntries->get(
            $team->getKnowledgebaseId(),
            null,
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $organization,
            $docs,
            $topics,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::SHOW_TEAM_DOC_ROUTE_NAME,
            ['team_url_id' => $teamUrlId]
        );

        $context = [
            'create_doc_route' => $createDocRoute,
            'create_topic_route' => $createTopicRoute,
            'knowledgebase_entries' => $knowledgebaseEntries,
            'html_title' => sprintf('Docs – %s', $team->getName()),
            'team' => $team,
            'current_user_is_in_team' => $currentUserIsInTeam,
        ];

        return new Response(
            $this->twig->render('team/team_docs.twig', $context)
        );
    }
}
