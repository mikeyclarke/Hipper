<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Topic;

use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Topic\TopicCreator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateTopicController
{
    use \Hipper\Api\ApiControllerTrait;

    private $knowledgebaseRouteUrlGenerator;
    private $topicCreator;

    public function __construct(
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        TopicCreator $topicCreator
    ) {
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
        $this->topicCreator = $topicCreator;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');

        try {
            list($model, $route, $knowledgebaseOwner) = $this->topicCreator->create(
                $currentUser,
                $request->request->all()
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route);
        return new JsonResponse(['topic_url' => $url], 201);
    }
}
