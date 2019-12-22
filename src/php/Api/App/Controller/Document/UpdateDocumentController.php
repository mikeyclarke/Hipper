<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Document;

use Hipper\Document\Document;
use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRepository;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateDocumentController
{
    use \Hipper\Api\ApiControllerTrait;

    private $document;
    private $documentRepository;
    private $knowledgebaseRouteUrlGenerator;

    public function __construct(
        Document $document,
        DocumentRepository $documentRepository,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator
    ) {
        $this->document = $document;
        $this->documentRepository = $documentRepository;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
    }

    public function postAction(Request $request): JsonResponse
    {
        $person = $request->attributes->get('person');
        $organization = $request->attributes->get('organization');
        $documentId = $request->attributes->get('document_id', null);

        $result = $this->documentRepository->findById($documentId, $person->getOrganizationId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $document = DocumentModel::createFromArray($result);

        try {
            list($route, $knowledgebaseOwner) = $this->document->update($person, $document, $request->request->all());
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route);
        return new JsonResponse(['doc_url' => $url]);
    }
}
