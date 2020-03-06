<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Knowledgebase;

use Hipper\Document\DocumentModel;
use Hipper\Document\DocumentRepository;
use Hipper\FrontEnd\App\Controller\Document\DocumentController;
use Hipper\FrontEnd\App\Controller\Topic\TopicController;
use Hipper\Organization\OrganizationModel;
use Hipper\Topic\TopicModel;
use Hipper\Topic\TopicRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentOrTopicControllerRouter
{
    private DocumentController $documentController;
    private DocumentRepository $documentRepository;
    private TopicController $topicController;
    private TopicRepository $topicRepository;

    public function __construct(
        DocumentController $documentController,
        DocumentRepository $documentRepository,
        TopicController $topicController,
        TopicRepository $topicRepository
    ) {
        $this->documentRepository = $documentRepository;
        $this->documentController = $documentController;
        $this->topicController = $topicController;
        $this->topicRepository = $topicRepository;
    }

    public function route(Request $request)
    {
        $entityType = $request->attributes->get('entity_type');
        $action = $request->attributes->get('action');
        $organization = $request->attributes->get('organization');

        switch ($entityType) {
            case 'document':
                $documentId = $request->attributes->get('document_id');
                $document = $this->getDocument($documentId, $organization);
                if (null === $document) {
                    throw new NotFoundHttpException;
                }
                $request->attributes->set('document', $document);
                return $this->documentController->$action($request);
            case 'topic':
                $topicId = $request->attributes->get('topic_id');
                $topic = $this->getTopic($topicId, $organization);
                if (null === $topic) {
                    throw new NotFoundHttpException;
                }
                $request->attributes->set('topic', $topic);
                return $this->topicController->$action($request);
            default:
                throw new RuntimeException('Unsupported knowledgebase route entity type');
        }
    }

    private function getDocument(string $documentId, OrganizationModel $organization): ?DocumentModel
    {
        $result = $this->documentRepository->findById($documentId, $organization->getId());
        if (null === $result) {
            return null;
        }
        return DocumentModel::createFromArray($result);
    }

    private function getTopic(string $topicId, OrganizationModel $organization): ?TopicModel
    {
        $result = $this->topicRepository->findById($topicId, $organization->getId());
        if (null === $result) {
            return null;
        }
        return TopicModel::createFromArray($result);
    }
}
