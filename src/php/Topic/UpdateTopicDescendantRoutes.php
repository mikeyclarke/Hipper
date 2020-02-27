<?php
declare(strict_types=1);

namespace Hipper\Topic;

use Doctrine\DBAL\Connection;
use Hipper\Document\DocumentModel;
use Hipper\Knowledgebase\Exception\UnsupportedKnowledgebaseContentTypeException;
use Hipper\Knowledgebase\KnowledgebaseRouteCreator;
use Hipper\Knowledgebase\KnowledgebaseRouteModel;
use RuntimeException;

class UpdateTopicDescendantRoutes
{
    private Connection $connection;
    private KnowledgebaseRouteCreator $knowledgebaseRouteCreator;
    private TopicRepository $topicRepository;

    public function __construct(
        Connection $connection,
        KnowledgebaseRouteCreator $knowledgebaseRouteCreator,
        TopicRepository $topicRepository
    ) {
        $this->connection = $connection;
        $this->knowledgebaseRouteCreator = $knowledgebaseRouteCreator;
        $this->topicRepository = $topicRepository;
    }

    public function update(TopicModel $topic, KnowledgebaseRouteModel $route): void
    {
        $descendants = $this->getTopicDescendants($topic);
        $topicRoutes = [];

        $topicRoutes[$topic->getId()] = $route;

        foreach ($descendants as $descendant) {
            if ($descendant['id'] === $topic->getId()) {
                continue;
            }

            if (!isset($topicRoutes[$descendant['parent_topic_id']])) {
                throw new RuntimeException('Parent topic not found');
            }

            $routePrefix = $topicRoutes[$descendant['parent_topic_id']]->getRoute() . '/';

            switch ($descendant['type']) {
                case 'document':
                    $descendant['topic_id'] = $descendant['parent_topic_id'];
                    $model = DocumentModel::createFromArray($descendant);
                    break;
                case 'topic':
                    $model = TopicModel::createFromArray($descendant);
                    break;
                default:
                    throw new UnsupportedKnowledgebaseContentTypeException;
            }

            $this->connection->beginTransaction();
            try {
                $route = $this->knowledgebaseRouteCreator->create(
                    $model,
                    $routePrefix . $model->getUrlSlug(),
                    true
                );

                $this->connection->commit();
            } catch (\Exception $e) {
                $this->connection->rollBack();
                throw $e;
            }

            if ($model instanceof TopicModel) {
                $topicRoutes[$model->getId()] = $route;
            }
        }
    }

    private function getTopicDescendants(TopicModel $topic): array
    {
        return $this->topicRepository->getTopicAndDescendants(
            $topic->getId(),
            $topic->getKnowledgebaseId(),
            $topic->getOrganizationId()
        );
    }
}
