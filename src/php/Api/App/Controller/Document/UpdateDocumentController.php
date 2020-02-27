<?php
declare(strict_types=1);

namespace Hipper\Api\App\Controller\Document;

use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRepository;
use Hipper\Document\DocumentUpdater;
use Hipper\Knowledgebase\KnowledgebaseRouteUrlGenerator;
use Hipper\Validation\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdateDocumentController
{
    use \Hipper\Api\ApiControllerTrait;

    private $documentRepository;
    private $documentUpdater;
    private $knowledgebaseRouteUrlGenerator;

    public function __construct(
        DocumentRepository $documentRepository,
        DocumentUpdater $documentUpdater,
        KnowledgebaseRouteUrlGenerator $knowledgebaseRouteUrlGenerator
    ) {
        $this->documentRepository = $documentRepository;
        $this->documentUpdater = $documentUpdater;
        $this->knowledgebaseRouteUrlGenerator = $knowledgebaseRouteUrlGenerator;
    }

    public function postAction(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('current_user');
        $organization = $request->attributes->get('organization');
        $documentId = $request->attributes->get('document_id', null);

        $result = $this->documentRepository->findById($documentId, $currentUser->getOrganizationId());
        if (null === $result) {
            throw new NotFoundHttpException;
        }

        $document = DocumentModel::createFromArray($result);

        try {
            list($route, $knowledgebaseOwner) = $this->documentUpdater->update(
                $currentUser,
                $document,
                $request->request->all()
            );
        } catch (ValidationException $e) {
            return $this->createValidationExceptionResponse($e);
        }

        $url = $this->knowledgebaseRouteUrlGenerator->generate($organization, $knowledgebaseOwner, $route);
        return new JsonResponse(['doc_url' => $url]);
    }
}
