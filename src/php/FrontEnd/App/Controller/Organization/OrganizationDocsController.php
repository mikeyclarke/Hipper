<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Organization;

use Hipper\Knowledgebase\KnowledgebaseEntries;
use Hipper\Knowledgebase\KnowledgebaseEntriesListFormatter;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class OrganizationDocsController
{
    private const CREATE_DOC_ROUTE_NAME = 'front_end.app.organization.doc.create';
    private const CREATE_TOPIC_ROUTE_NAME = 'front_end.app.organization.topic.create';

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
        $subdomain = $organization->getSubdomain();
        $timeZone = $this->timeZoneFromRequest->get($request);

        list($docs, $topics) = $this->knowledgebaseEntries->get(
            $organization->getKnowledgebaseId(),
            null,
            $organization->getId()
        );

        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $organization,
            $docs,
            $topics,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::SHOW_ORGANIZATION_DOC_ROUTE_NAME
        );

        $createDocRoute = $this->router->generate(self::CREATE_DOC_ROUTE_NAME, [
            'subdomain' => $subdomain,
        ]);
        $createTopicRoute = $this->router->generate(self::CREATE_TOPIC_ROUTE_NAME, [
            'subdomain' => $subdomain,
            'return_to' => $request->getRequestUri(),
        ]);

        $context = [
            'create_doc_route' => $createDocRoute,
            'create_topic_route' => $createTopicRoute,
            'knowledgebase_entries' => $knowledgebaseEntries,
            'html_title' => 'Docs',
        ];

        return new Response(
            $this->twig->render('organization/organization_docs.twig', $context)
        );
    }
}
