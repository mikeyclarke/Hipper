<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Document;

use Hipper\Document\Document;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CreateDocumentController
{
    use \Hipper\Api\ApiControllerTrait;

    private $document;
    private $knowledgebaseRouteUrlGenerator;

    public function __construct(
        Document $document,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator
    ) {
        $this->document = $document;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');

        try {
            list($route, $knowledgebaseOwner) = $this->document->create($person, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($knowledgebaseOwner, $route);
        return new JsonResponse(['doc_url' => $url], 201);
    }
}
