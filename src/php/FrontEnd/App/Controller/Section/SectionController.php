<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Section;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Knowledgebase\KnowledgebaseEntries;
use Hipper\Knowledgebase\KnowledgebaseEntriesListFormatter;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Organization\OrganizationModel;
use Hipper\Section\SectionModel;
use Hipper\Section\SectionRepository;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class SectionController
{
    private const CREATE_PROJECT_DOC_ROUTE_NAME = 'front_end.app.project.doc.create';
    private const CREATE_PROJECT_SECTION_ROUTE_NAME = 'front_end.app.project.section.create';
    private const CREATE_TEAM_DOC_ROUTE_NAME = 'front_end.app.team.doc.create';
    private const CREATE_TEAM_SECTION_ROUTE_NAME = 'front_end.app.team.section.create';

    private KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs;
    private KnowledgebaseEntries $knowledgebaseEntries;
    private KnowledgebaseEntriesListFormatter $knowledgebaseEntriesListFormatter;
    private SectionRepository $sectionRepository;
    private TimeZoneFromRequest $timeZoneFromRequest;
    private Twig $twig;
    private UrlGeneratorInterface $router;

    public function __construct(
        KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs,
        KnowledgebaseEntries $knowledgebaseEntries,
        KnowledgebaseEntriesListFormatter $knowledgebaseEntriesListFormatter,
        SectionRepository $sectionRepository,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig,
        UrlGeneratorInterface $router
    ) {
        $this->knowledgebaseBreadcrumbs = $knowledgebaseBreadcrumbs;
        $this->knowledgebaseEntries = $knowledgebaseEntries;
        $this->knowledgebaseEntriesListFormatter = $knowledgebaseEntriesListFormatter;
        $this->sectionRepository = $sectionRepository;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function getAction(Request $request): Response
    {
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');
        $knowledgebaseId = $request->attributes->get('knowledgebase_id');
        $organization = $request->attributes->get('organization');
        $sectionId = $request->attributes->get('section_id');
        $subdomain = $organization->getSubdomain();
        $timeZone = $this->timeZoneFromRequest->get($request);

        $result = $this->sectionRepository->findByIdInKnowledgebase(
            $sectionId,
            $knowledgebaseId,
            $organization->getId()
        );
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $section = SectionModel::createFromArray($result);
        $context = [
            'section' => $section,
            'html_title' => $section->getName(),
        ];

        switch ($knowledgebaseType) {
            case 'team':
                $knowledgebaseOwner = $request->attributes->get('team');
                $teamUrlId = $knowledgebaseOwner->getUrlId();
                $createDocRoute = $this->router->generate(self::CREATE_TEAM_DOC_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'team_url_id' => $teamUrlId,
                    'in' => $section->getId(),
                ]);
                $createSectionRoute = $this->router->generate(self::CREATE_TEAM_SECTION_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'team_url_id' => $teamUrlId,
                    'in' => $section->getId(),
                ]);
                $showDocRouteName = KnowledgebaseRouteUrlGenerator::SHOW_TEAM_DOC_ROUTE_NAME;
                $showDocRouteParams = ['team_url_id' => $teamUrlId];
                $twigTemplate = 'team/team_section.twig';

                $context['current_user_is_in_team'] = $request->attributes->get('current_user_is_in_team');
                $context['team'] = $knowledgebaseOwner;

                break;
            case 'project':
                $knowledgebaseOwner = $request->attributes->get('project');
                $projectUrlId = $knowledgebaseOwner->getUrlId();
                $createDocRoute = $this->router->generate(self::CREATE_PROJECT_DOC_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'project_url_id' => $projectUrlId,
                    'in' => $section->getId(),
                ]);
                $createSectionRoute = $this->router->generate(self::CREATE_PROJECT_SECTION_ROUTE_NAME, [
                    'subdomain' => $subdomain,
                    'project_url_id' => $projectUrlId,
                    'in' => $section->getId(),
                ]);
                $showDocRouteName = KnowledgebaseRouteUrlGenerator::SHOW_PROJECT_DOC_ROUTE_NAME;
                $showDocRouteParams = ['project_url_id' => $knowledgebaseOwner->getUrlId()];
                $twigTemplate = 'project/project_section.twig';

                $context['current_user_is_in_project'] = $request->attributes->get('current_user_is_in_project');
                $context['project'] = $knowledgebaseOwner;

                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        list($docs, $sections) = $this->knowledgebaseEntries->get(
            $knowledgebaseOwner->getKnowledgebaseId(),
            $section->getId(),
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $organization,
            $docs,
            $sections,
            $timeZone,
            $showDocRouteName,
            $showDocRouteParams
        );

        $breadcrumbs = $this->knowledgebaseBreadcrumbs->get(
            $organization,
            $knowledgebaseOwner,
            $section->getName(),
            $section->getParentSectionId()
        );

        $context['create_doc_route'] = $createDocRoute;
        $context['create_section_route'] = $createSectionRoute;
        $context['breadcrumbs'] = $breadcrumbs;
        $context['knowledgebase_entries'] = $knowledgebaseEntries;

        return new Response(
            $this->twig->render($twigTemplate, $context)
        );
    }
}
