<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Section;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Hipper\Project\ProjectModel;
use Hipper\Section\Section;
use Hipper\Section\SectionModel;
use Hipper\Section\SectionRepository;
use Hipper\Team\TeamModel;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class UpdateSectionController
{
    use \Hipper\Api\ApiControllerTrait;

    private const CREATE_PROJECT_DOC_ROUTE_NAME = 'front_end.app.project.doc.create';
    private const CREATE_PROJECT_SECTION_ROUTE_NAME = 'front_end.app.project.section.create';
    private const CREATE_TEAM_DOC_ROUTE_NAME = 'front_end.app.team.doc.create';
    private const CREATE_TEAM_SECTION_ROUTE_NAME = 'front_end.app.team.section.create';

    private KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs;
    private KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator;
    private Section $section;
    private SectionRepository $sectionRepository;
    private Twig $twig;
    private UrlGeneratorInterface $router;

    public function __construct(
        KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        Section $section,
        SectionRepository $sectionRepository,
        Twig $twig,
        UrlGeneratorInterface $router
    ) {
        $this->knowledgebaseBreadcrumbs = $knowledgebaseBreadcrumbs;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
        $this->section = $section;
        $this->sectionRepository = $sectionRepository;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');
        $subdomain = $organization->getSubdomain();
        $sectionId = $request->attributes->get('section_id', null);

        $result = $this->sectionRepository->findById($sectionId, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $section = SectionModel::createFromArray($result);

        try {
            list($section, $route, $knowledgebaseOwner) = $this->section->update(
                $currentUser,
                $section,
                $request->request->all()
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route);

        $breadcrumbs = $this->knowledgebaseBreadcrumbs->get(
            $organization,
            $knowledgebaseOwner,
            $section->getName(),
            $section->getParentSectionId()
        );

        $twigContext = [
            'breadcrumbs' => $breadcrumbs,
            'create_doc_route' => $this->getCreateDocRoute($knowledgebaseOwner, $section, $subdomain),
            'create_section_route' => $this->getCreateSubSectionRoute($knowledgebaseOwner, $section, $subdomain),
            'is_editable' => true,
            'mode' => 'view',
            'section' => $section,
        ];

        return new JsonResponse([
            'header_html' => $this->twig->render('section/_section_header.twig', $twigContext),
            'section_url' => $url,
        ]);
    }

    private function getCreateDocRoute(
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        SectionModel $section,
        string $subdomain
    ): string {
        $parameters = [
            'subdomain' => $subdomain,
            'in' => $section->getId(),
        ];

        $class = get_class($knowledgebaseOwner);
        switch ($class) {
            case TeamModel::class:
                $routeName = self::CREATE_TEAM_DOC_ROUTE_NAME;
                $routeParams = array_merge($parameters, ['team_url_id' => $knowledgebaseOwner->getUrlId()]);
                break;
            case ProjectModel::class:
                $routeName = self::CREATE_PROJECT_DOC_ROUTE_NAME;
                $routeParams = array_merge($parameters, ['project_url_id' => $knowledgebaseOwner->getUrlId()]);
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        return $this->router->generate($routeName, $routeParams);
    }

    private function getCreateSubSectionRoute(
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        SectionModel $section,
        string $subdomain
    ): string {
        $parameters = [
            'subdomain' => $subdomain,
            'in' => $section->getId(),
        ];

        $class = get_class($knowledgebaseOwner);
        switch ($class) {
            case TeamModel::class:
                $routeName = self::CREATE_TEAM_SECTION_ROUTE_NAME;
                $routeParams = array_merge($parameters, ['team_url_id' => $knowledgebaseOwner->getUrlId()]);
                break;
            case ProjectModel::class:
                $routeName = self::CREATE_PROJECT_SECTION_ROUTE_NAME;
                $routeParams = array_merge($parameters, ['project_url_id' => $knowledgebaseOwner->getUrlId()]);
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        return $this->router->generate($routeName, $routeParams);
    }
}
