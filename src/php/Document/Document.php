<?php
declare(strict_types=1);

namespace Hipper\Document;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Person\PersonModel;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;

class Document
{
    private $connection;
    private $documentDescriptionDeducer;
    private $documentInserter;
    private $documentRevision;
    private $documentValidator;
    private $idGenerator;
    private $knowledgebaseRoute;
    private $urlIdGenerator;
    private $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        DocumentDescriptionDeducer $documentDescriptionDeducer,
        DocumentInserter $documentInserter,
        DocumentRevision $documentRevision,
        DocumentValidator $documentValidator,
        IdGenerator $idGenerator,
        KnowledgebaseRoute $knowledgebaseRoute,
        UrlIdGenerator $urlIdGenerator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->documentDescriptionDeducer = $documentDescriptionDeducer;
        $this->documentInserter = $documentInserter;
        $this->documentRevision = $documentRevision;
        $this->documentValidator = $documentValidator;
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseRoute = $knowledgebaseRoute;
        $this->urlIdGenerator = $urlIdGenerator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): DocumentModel
    {
        $this->documentValidator->validate($parameters, $person->getOrganizationId(), true);

        $id = $this->idGenerator->generate();
        $urlSlug = $this->urlSlugGenerator->generateFromString($parameters['name']);
        $urlId = $this->urlIdGenerator->generate();

        $deducedDescription = null;
        if (isset($parameters['content']) && is_array($parameters['content'])) {
            $deducedDescription = $this->documentDescriptionDeducer->deduce($parameters['content']);
        }

        $content = null;
        if (isset($parameters['content']) && is_array($parameters['content'])) {
            $content = json_encode($parameters['content']);
        }

        $this->connection->beginTransaction();
        try {
            $result = $this->documentInserter->insert(
                $id,
                $parameters['name'],
                $urlSlug,
                $urlId,
                $parameters['knowledgebase_id'],
                $person->getOrganizationId(),
                $person->getId(),
                $parameters['description'] ?? null,
                $deducedDescription,
                $content
            );
            $model = DocumentModel::createFromArray($result);

            $this->knowledgebaseRoute->createForDocument(
                $model,
                $model->getUrlSlug(),
                true,
                true
            );
            $this->documentRevision->create($model);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return $model;
    }
}
