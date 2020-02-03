<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Hipper\Knowledgebase\KnowledgebaseEntries;
use Hipper\Knowledgebase\KnowledgebaseEntriesListFormatter;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class ProjectDocsController
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
        $project = $request->attributes->get('project');
        $currentUserIsInProject = $request->attributes->get('current_user_is_in_project');

        list($docs, $sections) = $this->knowledgebaseEntries->get(
            $project->getKnowledgebaseId(),
            null,
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $organization,
            $docs,
            $sections,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::SHOW_PROJECT_DOC_ROUTE_NAME,
            ['project_url_id' => $project->getUrlId()]
        );

        $context = [
            'knowledgebaseEntries' => $knowledgebaseEntries,
            'html_title' => sprintf('Docs – %s', $project->getName()),
            'project' => $project,
            'current_user_is_in_project' => $currentUserIsInProject,
        ];

        return new Response(
            $this->twig->render('project/project_docs.twig', $context)
        );
    }
}
