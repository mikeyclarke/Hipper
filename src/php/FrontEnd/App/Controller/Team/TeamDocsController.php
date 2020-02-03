<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Team;

use Hipper\Knowledgebase\KnowledgebaseEntries;
use Hipper\Knowledgebase\KnowledgebaseEntriesListFormatter;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class TeamDocsController
{
    private $knowledgebaseEntries;
    private $knowledgebaseEntriesListFormatter;
    private $timeZoneFromRequest;
    private $twig;

    public function __construct(
        KnowledgebaseEntries $knowledgebaseEntries,
        KnowledgebaseEntriesListFormatter $knowledgebaseEntriesListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->knowledgebaseEntries = $knowledgebaseEntries;
        $this->knowledgebaseEntriesListFormatter = $knowledgebaseEntriesListFormatter;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $team = $request->attributes->get('team');
        $currentUserIsInTeam = $request->attributes->get('current_user_is_in_team');

        list($docs, $sections) = $this->knowledgebaseEntries->get(
            $team->getKnowledgebaseId(),
            null,
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $organization,
            $docs,
            $sections,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::SHOW_TEAM_DOC_ROUTE_NAME,
            ['team_url_id' => $team->getUrlId()]
        );

        $context = [
            'knowledgebaseEntries' => $knowledgebaseEntries,
            'html_title' => sprintf('Docs – %s', $team->getName()),
            'team' => $team,
            'current_user_is_in_team' => $currentUserIsInTeam,
        ];

        return new Response(
            $this->twig->render('team/team_docs.twig', $context)
        );
    }
}
