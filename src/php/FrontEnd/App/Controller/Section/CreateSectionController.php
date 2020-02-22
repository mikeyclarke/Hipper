<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Section;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Security\UntrustedInternalUriRedirector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Validation;
use Twig\Environment as Twig;

class CreateSectionController
{
    private const DEFAULT_NAME = 'Untitled section';

    private KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs;
    private Twig $twig;
    private UntrustedInternalUriRedirector $untrustedInternalUriRedirector;

    public function __construct(
        KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs,
        Twig $twig,
        UntrustedInternalUriRedirector $untrustedInternalUriRedirector
    ) {
        $this->knowledgebaseBreadcrumbs = $knowledgebaseBreadcrumbs;
        $this->twig = $twig;
        $this->untrustedInternalUriRedirector = $untrustedInternalUriRedirector;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $knowledgebaseType = $request->attributes->get('knowledgebase_type');
        $returnTo = $request->query->get('return_to');

        switch ($knowledgebaseType) {
            case 'project':
                $twigTemplate = 'project/create_section.twig';
                $context = $this->getProjectGetActionContext($request);
                $knowledgebaseOwner = $context['project'];
                break;
            case 'team':
                $twigTemplate = 'team/create_section.twig';
                $context = $this->getTeamGetActionContext($request);
                $knowledgebaseOwner = $context['team'];
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        $breadcrumbs = $this->knowledgebaseBreadcrumbs->get(
            $organization,
            $knowledgebaseOwner,
            self::DEFAULT_NAME,
            $context['parent_section_id']
        );
        $context['breadcrumbs'] = $breadcrumbs;
        $context['back_link'] = $this->untrustedInternalUriRedirector->generateUri($returnTo, '.');

        return new Response(
            $this->twig->render($twigTemplate, $context)
        );
    }

    private function getProjectGetActionContext(Request $request): array
    {
        $project = $request->attributes->get('project');
        $currentUserIsInProject = $request->attributes->get('current_user_is_in_project');

        return [
            'knowledgebase_id' => $project->getKnowledgebaseId(),
            'current_user_is_in_project' => $currentUserIsInProject,
            'parent_section_id' => $request->query->get('in', null),
            'project' => $project,
        ];
    }

    private function getTeamGetActionContext(Request $request): array
    {
        $team = $request->attributes->get('team');
        $currentUserIsInTeam = $request->attributes->get('current_user_is_in_team');

        return [
            'knowledgebase_id' => $team->getKnowledgebaseId(),
            'current_user_is_in_team' => $currentUserIsInTeam,
            'parent_section_id' => $request->query->get('in', null),
            'team' => $team,
        ];
    }

    private function isUuid(string $parentSectionId): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($parentSectionId, [new Uuid]);
        return count($violations) === 0;
    }
}
