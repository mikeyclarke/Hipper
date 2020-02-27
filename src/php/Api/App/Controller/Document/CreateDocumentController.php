<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Document;

use Hipper\Document\DocumentCreator;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateDocumentController
{
    use \Hipper\Api\ApiControllerTrait;

    private $documentCreator;
    private $knowledgebaseRouteUrlGenerator;

    public function __construct(
        DocumentCreator $documentCreator,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator
    ) {
        $this->documentCreator = $documentCreator;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');

        try {
            list($route, $knowledgebaseOwner) = $this->documentCreator->create($currentUser, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route);
        return new JsonResponse(['doc_url' => $url], 201);
    }
}
