<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Topic;

use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Topic\Topic;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateTopicController
{
    use \Hipper\Api\ApiControllerTrait;

    private $knowledgebaseRouteUrlGenerator;
    private $topic;

    public function __construct(
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator,
        Topic $topic
    ) {
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
        $this->topic = $topic;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');

        try {
            list($model, $route, $knowledgebaseOwner) = $this->topic->create($currentUser, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route);
        return new JsonResponse(['topic_url' => $url], 201);
    }
}
