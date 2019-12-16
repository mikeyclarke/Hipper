<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Section;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
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
use Twig\Environment as Twig;

class SectionController
{
    private $knowledgebaseEntries;
    private $knowledgebaseEntriesListFormatter;
    private $sectionRepository;
    private $timeZoneFromRequest;
    private $twig;

    public function __construct(
        KnowledgebaseEntries $knowledgebaseEntries,
        KnowledgebaseEntriesListFormatter $knowledgebaseEntriesListFormatter,
        SectionRepository $sectionRepository,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->knowledgebaseEntries = $knowledgebaseEntries;
        $this->knowledgebaseEntriesListFormatter = $knowledgebaseEntriesListFormatter;
        $this->sectionRepository = $sectionRepository;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');
        $knowledgebaseId = $request->attributes->get('knowledgebase_id');
        $organization = $request->attributes->get('organization');
        $sectionId = $request->attributes->get('section_id');
        $timeZone = $this->timeZoneFromRequest->get($request);

        $result = $this->sectionRepository->findById($sectionId, $knowledgebaseId, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $section = SectionModel::createFromArray($result);

        switch ($knowledgebaseType) {
            case 'team':
                $twigTemplate = 'team/team_section.twig';
                list($knowledgebaseOwnerContext, $knowledgebaseEntries) = $this->getTeamContext(
                    $request,
                    $section,
                    $organization,
                    $timeZone
                );
                break;
            case 'project':
                $twigTemplate = 'project/project_section.twig';
                list($knowledgebaseOwnerContext, $knowledgebaseEntries) = $this->getProjectContext(
                    $request,
                    $section,
                    $organization,
                    $timeZone
                );
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        $context = array_merge(
            $knowledgebaseOwnerContext,
            [
                'knowledgebaseEntries' => $knowledgebaseEntries,
                'section' => $section,
                'html_title' => $section->getName(),
            ]
        );

        return new Response(
            $this->twig->render($twigTemplate, $context)
        );
    }

    private function getTeamContext(
        Request $request,
        SectionModel $section,
        OrganizationModel $organization,
        string $timeZone
    ): array {
        $team = $request->attributes->get('team');
        $personIsInTeam = $request->attributes->get('personIsInTeam');

        list($docs, $sections) = $this->knowledgebaseEntries->get(
            $team->getKnowledgebaseId(),
            $section->getId(),
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $docs,
            $sections,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::SHOW_TEAM_DOC_ROUTE_NAME,
            ['team_url_id' => $team->getUrlId()]
        );

        $context = [
            'team' => $team,
            'personIsInTeam' => $personIsInTeam,
        ];

        return [$context, $knowledgebaseEntries];
    }

    private function getProjectContext(
        Request $request,
        SectionModel $section,
        OrganizationModel $organization,
        string $timeZone
    ): array {
        $project = $request->attributes->get('project');
        $personIsInProject = $request->attributes->get('personIsInProject');

        list($docs, $sections) = $this->knowledgebaseEntries->get(
            $project->getKnowledgebaseId(),
            $section->getId(),
            $organization->getId()
        );

        $timeZone = $this->timeZoneFromRequest->get($request);
        $knowledgebaseEntries = $this->knowledgebaseEntriesListFormatter->format(
            $docs,
            $sections,
            $timeZone,
            KnowledgebaseRouteUrlGenerator::SHOW_PROJECT_DOC_ROUTE_NAME,
            ['project_url_id' => $project->getUrlId()]
        );

        $context = [
            'project' => $project,
            'personIsInProject' => $personIsInProject,
        ];

        return [$context, $knowledgebaseEntries];
    }
}
