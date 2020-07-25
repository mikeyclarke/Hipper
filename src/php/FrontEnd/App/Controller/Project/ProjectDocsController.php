<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Project;

use Hipper\Knowledgebase\KnowledgebaseEntries;
use Hipper\Knowledgebase\KnowledgebaseEntriesListFormatter;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class ProjectDocsController
{
    private const CREATE_DOC_ROUTE_NAME = 'front_end.app.project.doc.create';
    private const CREATE_TOPIC_ROUTE_NAME = 'front_end.app.project.topic.create';

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
        $project = $request->attributes->get('project');
        $currentUserIsInProject = $request->attributes->get('current_user_is_in_project');
        $projectUrlSlug = $project->getUrlSlug();
        $subdomain = $organization->getSubdomain();

        $createDocRoute = $this->router->generate(self::CREATE_DOC_ROUTE_NAME, [
            'project_url_slug' => $projectUrlSlug,
            'subdomain' => $subdomain,
        ]);
        $createTopicRoute = $this->router->generate(self::CREATE_TOPIC_ROUTE_NAME, [
            'project_url_slug' => $projectUrlSlug,
            'subdomain' => $subdomain,
            'return_to' => $request->getRequestUri(),
        ]);

        list($docs, $topics) = $this->knowledgebaseEntries->get(
            $project->getKnowledgebaseId(),
            null,
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $organization,
            $docs,
            $topics,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::SHOW_PROJECT_DOC_ROUTE_NAME,
            ['project_url_slug' => $projectUrlSlug]
        );

        $context = [
            'create_doc_route' => $createDocRoute,
            'create_topic_route' => $createTopicRoute,
            'knowledgebase_entries' => $knowledgebaseEntries,
            'html_title' => sprintf('Docs – %s', $project->getName()),
            'project' => $project,
            'current_user_is_in_project' => $currentUserIsInProject,
        ];

        return new Response(
            $this->twig->render('project/project_docs.twig', $context)
        );
    }
}
