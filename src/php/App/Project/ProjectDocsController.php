<?php
declare(strict_types=1);

namespace Hipper\App\Project;

use Hipper\Document\DocumentListFormatter;
use Hipper\Document\DocumentRepository;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

class ProjectDocsController
{
    private $documentListFormatter;
    private $documentRepository;
    private $timeZoneFromRequest;
    private $twig;

    public function __construct(
        DocumentListFormatter $documentListFormatter,
        DocumentRepository $documentRepository,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig_Environment $twig
    ) {
        $this->documentListFormatter = $documentListFormatter;
        $this->documentRepository = $documentRepository;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $project = $request->attributes->get('project');
        $personIsInProject = $request->attributes->get('personIsInProject');

        $docs = $this->documentRepository->getAllForKnowledgebaseInSection(
            $project->getKnowledgebaseId(),
            null,
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $documentList = $this->documentListFormatter->format(
            $docs,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::GET_PROJECT_DOC_ROUTE_NAME,
            ['project_url_id' => $project->getUrlId()]
        );

        $context = [
            'documentList' => $documentList,
            'html_title' => sprintf('Docs – %s', $project->getName()),
            'project' => $project,
            'personIsInProject' => $personIsInProject,
        ];

        return new Response(
            $this->twig->render('project/project_docs.twig', $context)
        );
    }
}
