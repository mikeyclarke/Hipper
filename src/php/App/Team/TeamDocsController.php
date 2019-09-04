<?php
declare(strict_types=1);

namespace Hipper\App\Team;

use Hipper\Document\DocumentListFormatter;
use Hipper\Document\DocumentRepository;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class TeamDocsController
{
    private $documentListFormatter;
    private $documentRepository;
    private $timeZoneFromRequest;
    private $twig;

    public function __construct(
        DocumentListFormatter $documentListFormatter,
        DocumentRepository $documentRepository,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->documentListFormatter = $documentListFormatter;
        $this->documentRepository = $documentRepository;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $team = $request->attributes->get('team');
        $personIsInTeam = $request->attributes->get('personIsInTeam');

        $docs = $this->documentRepository->getAllForKnowledgebaseInSection(
            $team->getKnowledgebaseId(),
            null,
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $documentList = $this->documentListFormatter->format(
            $docs,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::GET_TEAM_DOC_ROUTE_NAME,
            ['team_url_id' => $team->getUrlId()]
        );

        $context = [
            'documentList' => $documentList,
            'html_title' => sprintf('Docs – %s', $team->getName()),
            'team' => $team,
            'personIsInTeam' => $personIsInTeam,
        ];

        return new Response(
            $this->twig->render('team/team_docs.twig', $context)
        );
    }
}
