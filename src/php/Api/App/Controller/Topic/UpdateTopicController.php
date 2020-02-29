<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Topic;

use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseEntityException;
use Hipper\Knowledgebase\KnowledgebaseBreadcrumbs;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Knowledgebase\KnowledgebaseOwnerModelInterface;
use Hipper\Organization\OrganizationModel;
use Hipper\Project\ProjectModel;
use Hipper\Team\TeamModel;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use Hipper\Topic\TopicUpdater;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment as Twig;

class UpdateTopicController
{
    use \Hipper\Api\ApiControllerTrait;

    private const CREATE_ORGANIZATION_DOC_ROUTE_NAME = 'front_end.app.organization.doc.create';
    private const CREATE_ORGANIZATION_TOPIC_ROUTE_NAME = 'front_end.app.organization.topic.create';
    private const CREATE_PROJECT_DOC_ROUTE_NAME = 'front_end.app.project.doc.create';
    private const CREATE_PROJECT_TOPIC_ROUTE_NAME = 'front_end.app.project.topic.create';
    private const CREATE_TEAM_DOC_ROUTE_NAME = 'front_end.app.team.doc.create';
    private const CREATE_TEAM_TOPIC_ROUTE_NAME = 'front_end.app.team.topic.create';

    private KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs;
    private KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator;
    private TopicRepository $topicRepository;
    private TopicUpdater $topicUpdater;
    private Twig $twig;
    private UrlGeneratorInterface $router;

    public function __construct(
        KnowledgebaseBreadcrumbs $knowledgebaseBreadcrumbs,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        TopicRepository $topicRepository,
        TopicUpdater $topicUpdater,
        Twig $twig,
        UrlGeneratorInterface $router
    ) {
        $this->knowledgebaseBreadcrumbs = $knowledgebaseBreadcrumbs;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
        $this->topicRepository = $topicRepository;
        $this->topicUpdater = $topicUpdater;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');
        $subdomain = $organization->getSubdomain();
        $topicId = $request->attributes->get('topic_id', null);

        $result = $this->topicRepository->findById($topicId, $organization->getId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $topic = TopicModel::createFromArray($result);

        try {
            list($topic, $route, $knowledgebaseOwner) = $this->topicUpdater->update(
                $currentUser,
                $topic,
                $request->request->all()
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route);

        $breadcrumbs = $this->knowledgebaseBreadcrumbs->get(
            $organization,
            $knowledgebaseOwner,
            $topic->getName(),
            $topic->getParentTopicId()
        );

        $twigContext = [
            'breadcrumbs' => $breadcrumbs,
            'create_doc_route' => $this->getCreateDocRoute($knowledgebaseOwner, $topic, $subdomain),
            'create_topic_route' => $this->getCreateSubTopicRoute($knowledgebaseOwner, $topic, $subdomain),
            'is_editable' => true,
            'mode' => 'view',
            'topic' => $topic,
        ];

        return new JsonResponse([
            'header_html' => $this->twig->render('topic/_topic_header.twig', $twigContext),
            'topic_url' => $url,
        ]);
    }

    private function getCreateDocRoute(
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        TopicModel $topic,
        string $subdomain
    ): string {
        $parameters = [
            'subdomain' => $subdomain,
            'in' => $topic->getId(),
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
            case OrganizationModel::class:
                $routeName = self::CREATE_ORGANIZATION_DOC_ROUTE_NAME;
                $routeParams = $parameters;
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        return $this->router->generate($routeName, $routeParams);
    }

    private function getCreateSubTopicRoute(
        KnowledgebaseOwnerModelInterface $knowledgebaseOwner,
        TopicModel $topic,
        string $subdomain
    ): string {
        $parameters = [
            'subdomain' => $subdomain,
            'in' => $topic->getId(),
        ];

        $class = get_class($knowledgebaseOwner);
        switch ($class) {
            case TeamModel::class:
                $routeName = self::CREATE_TEAM_TOPIC_ROUTE_NAME;
                $routeParams = array_merge($parameters, ['team_url_id' => $knowledgebaseOwner->getUrlId()]);
                break;
            case ProjectModel::class:
                $routeName = self::CREATE_PROJECT_TOPIC_ROUTE_NAME;
                $routeParams = array_merge($parameters, ['project_url_id' => $knowledgebaseOwner->getUrlId()]);
                break;
            case OrganizationModel::class:
                $routeName = self::CREATE_ORGANIZATION_TOPIC_ROUTE_NAME;
                $routeParams = $parameters;
                break;
            default:
                throw new UnsupportedKnowledgebaseEntityException;
        }

        return $this->router->generate($routeName, $routeParams);
    }
}
