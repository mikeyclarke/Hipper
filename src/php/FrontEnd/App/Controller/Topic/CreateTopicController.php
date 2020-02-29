<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Topic;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Organization\OrganizationModel;
use Hipper\Security\UntrustedInternalUriRedirector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\Validation;
use Twig\Environment as Twig;

class CreateTopicController
{
    private const DEFAULT_NAME = 'Untitled topic';

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

        $context = [
            'parent_topic_id' => $request->query->get('in', null),
        ];

        switch ($knowledgebaseType) {
            case 'project':
                $twigTemplate = 'project/create_topic.twig';
                $context = array_merge($context, $this->getProjectContext($request, $organization));
                $knowledgebaseOwner = $context['project'];
                break;
            case 'team':
                $twigTemplate = 'team/create_topic.twig';
                $context = array_merge($context, $this->getTeamContext($request, $organization));
                $knowledgebaseOwner = $context['team'];
                break;
            case 'organization':
                $twigTemplate = 'organization/create_topic.twig';
                $context = array_merge($context, $this->getOrganizationContext($request, $organization));
                $knowledgebaseOwner = $organization;
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        $breadcrumbs = $this->knowledgebaseBreadcrumbs->get(
            $organization,
            $knowledgebaseOwner,
            self::DEFAULT_NAME,
            $context['parent_topic_id']
        );
        $context['breadcrumbs'] = $breadcrumbs;
        $context['back_link'] = $this->untrustedInternalUriRedirector->generateUri($returnTo, '.');

        return new Response(
            $this->twig->render($twigTemplate, $context)
        );
    }

    private function getProjectContext(Request $request, OrganizationModel $organization): array
    {
        $project = $request->attributes->get('project');
        $currentUserIsInProject = $request->attributes->get('current_user_is_in_project');

        return [
            'knowledgebase_id' => $project->getKnowledgebaseId(),
            'current_user_is_in_project' => $currentUserIsInProject,
            'project' => $project,
        ];
    }

    private function getTeamContext(Request $request, OrganizationModel $organization): array
    {
        $team = $request->attributes->get('team');
        $currentUserIsInTeam = $request->attributes->get('current_user_is_in_team');

        return [
            'knowledgebase_id' => $team->getKnowledgebaseId(),
            'current_user_is_in_team' => $currentUserIsInTeam,
            'team' => $team,
        ];
    }

    private function getOrganizationContext(Request $request, OrganizationModel $organization): array
    {
        return [
            'knowledgebase_id' => $organization->getKnowledgebaseId(),
        ];
    }

    private function isUuid(string $parentTopicId): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($parentTopicId, [new Uuid]);
        return count($violations) === 0;
    }
}
