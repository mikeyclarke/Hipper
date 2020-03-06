<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Topic;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Knowledgebase\KnowledgebaseEntries;
use Hipper\Knowledgebase\KnowledgebaseEntriesListFormatter;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Organization\OrganizationModel;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class TopicController
{
    private const CREATE_ORGANIZATION_DOC_ROUTE_NAME = 'front_end.app.organization.doc.create';
    private const CREATE_ORGANIZATION_TOPIC_ROUTE_NAME = 'front_end.app.organization.topic.create';
    private const CREATE_PROJECT_DOC_ROUTE_NAME = 'front_end.app.project.doc.create';
    private const CREATE_PROJECT_TOPIC_ROUTE_NAME = 'front_end.app.project.topic.create';
    private const CREATE_TEAM_DOC_ROUTE_NAME = 'front_end.app.team.doc.create';
    private const CREATE_TEAM_TOPIC_ROUTE_NAME = 'front_end.app.team.topic.create';

    private KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs;
    private KnowledgebaseEntries $knowledgebaseEntries;
    private KnowledgebaseEntriesListFormatter $knowledgebaseEntriesListFormatter;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;
    private UrlGeneratorInterface $router;

    public function __construct(
        KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs,
        KnowledgebaseEntries $knowledgebaseEntries,
        KnowledgebaseEntriesListFormatter $knowledgebaseEntriesListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig,
        UrlGeneratorInterface $router
    ) {
        $this->knowledgebaseBreadcrumbs = $knowledgebaseBreadcrumbs;
        $this->knowledgebaseEntries = $knowledgebaseEntries;
        $this->knowledgebaseEntriesListFormatter = $knowledgebaseEntriesListFormatter;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function getAction(Request $request): Response
    {
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');
        $organization = $request->attributes->get('organization');
        $topic = $request->attributes->get('topic');
        $subdomain = $organization->getSubdomain();
        $timeZone = $this->timeZoneFromRequest->get($request);

        $context = [
            'topic' => $topic,
            'html_title' => $topic->getName(),
        ];

        switch ($knowledgebaseType) {
            case 'team':
                $knowledgebaseOwner = $request->attributes->get('team');
                $teamUrlId = $knowledgebaseOwner->getUrlId();
                $createDocRoute = $this->router->generate(self::CREATE_TEAM_DOC_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'team_url_id' => $teamUrlId,
                    'in' => $topic->getId(),
                ]);
                $createTopicRoute = $this->router->generate(self::CREATE_TEAM_TOPIC_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'team_url_id' => $teamUrlId,
                    'in' => $topic->getId(),
                    'return_to' => $request->getRequestUri(),
                ]);
                $showDocRouteName = KnowledgebaseRouteUrlGenerator::SHOW_TEAM_DOC_ROUTE_NAME;
                $showDocRouteParams = ['team_url_id' => $teamUrlId];
                $twigTemplate = 'team/team_topic.twig';

                $context['current_user_is_in_team'] = $request->attributes->get('current_user_is_in_team');
                $context['team'] = $knowledgebaseOwner;

                break;
            case 'project':
                $knowledgebaseOwner = $request->attributes->get('project');
                $projectUrlId = $knowledgebaseOwner->getUrlId();
                $createDocRoute = $this->router->generate(self::CREATE_PROJECT_DOC_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'project_url_id' => $projectUrlId,
                    'in' => $topic->getId(),
                ]);
                $createTopicRoute = $this->router->generate(self::CREATE_PROJECT_TOPIC_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'project_url_id' => $projectUrlId,
                    'in' => $topic->getId(),
                    'return_to' => $request->getRequestUri(),
                ]);
                $showDocRouteName = KnowledgebaseRouteUrlGenerator::SHOW_PROJECT_DOC_ROUTE_NAME;
                $showDocRouteParams = ['project_url_id' => $knowledgebaseOwner->getUrlId()];
                $twigTemplate = 'project/project_topic.twig';

                $context['current_user_is_in_project'] = $request->attributes->get('current_user_is_in_project');
                $context['project'] = $knowledgebaseOwner;

                break;
            case 'organization':
                $knowledgebaseOwner = $organization;
                $createDocRoute = $this->router->generate(self::CREATE_ORGANIZATION_DOC_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'in' => $topic->getId(),
                ]);
                $createTopicRoute = $this->router->generate(self::CREATE_ORGANIZATION_TOPIC_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'in' => $topic->getId(),
                    'return_to' => $request->getRequestUri(),
                ]);
                $showDocRouteName = KnowledgebaseRouteUrlGenerator::SHOW_ORGANIZATION_DOC_ROUTE_NAME;
                $showDocRouteParams = [];
                $twigTemplate = 'organization/organization_topic.twig';

                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        list($docs, $topics) = $this->knowledgebaseEntries->get(
            $knowledgebaseOwner->getKnowledgebaseId(),
            $topic->getId(),
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $organization,
            $docs,
            $topics,
            $timeZone,
            $showDocRouteName,
            $showDocRouteParams
        );

        $breadcrumbs = $this->knowledgebaseBreadcrumbs->get(
            $organization,
            $knowledgebaseOwner,
            $topic->getName(),
            $topic->getParentTopicId()
        );

        $context['create_doc_route'] = $createDocRoute;
        $context['create_topic_route'] = $createTopicRoute;
        $context['breadcrumbs'] = $breadcrumbs;
        $context['knowledgebase_entries'] = $knowledgebaseEntries;

        return new Response(
            $this->twig->render($twigTemplate, $context)
        );
    }
}
