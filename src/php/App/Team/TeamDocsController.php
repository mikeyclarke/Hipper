<?php
declare(strict_types=1);

namespace Hipper\App\Team;

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
        $personIsInTeam = $request->attributes->get('personIsInTeam');

        list($docs, $sections) = $this->knowledgebaseEntries->get(
            $team->getKnowledgebaseId(),
            null,
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $docs,
            $sections,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::GET_TEAM_DOC_ROUTE_NAME,
            ['team_url_id' => $team->getUrlId()]
        );

        $context = [
            'knowledgebaseEntries' => $knowledgebaseEntries,
            'html_title' => sprintf('Docs – %s', $team->getName()),
            'team' => $team,
            'personIsInTeam' => $personIsInTeam,
        ];

        return new Response(
            $this->twig->render('team/team_docs.twig', $context)
        );
    }
}
