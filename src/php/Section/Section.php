<?php
declare(strict_types=1);

namespace Hipper\Section;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\KnowledgebaseModel;
use Hipper\Knowledgebase\KnowledgebaseOwner;
use Hipper\Knowledgebase\KnowledgebaseRepository;
use Hipper\Knowledgebase\KnowledgebaseRoute;
use Hipper\Person\PersonModel;
use Hipper\Url\UrlIdGenerator;
use Hipper\Url\UrlSlugGenerator;

class Section
{
    private $connection;
    private $idGenerator;
    private $knowledgebaseOwner;
    private $knowledgebaseRepository;
    private $knowledgebaseRoute;
    private $sectionInserter;
    private $sectionValidator;
    private $urlIdGenerator;
    private $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        IdGenerator $idGenerator,
        KnowledgebaseOwner $knowledgebaseOwner,
        KnowledgebaseRepository $knowledgebaseRepository,
        KnowledgebaseRoute $knowledgebaseRoute,
        SectionInserter $sectionInserter,
        SectionValidator $sectionValidator,
        UrlIdGenerator $urlIdGenerator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->idGenerator = $idGenerator;
        $this->knowledgebaseOwner = $knowledgebaseOwner;
        $this->knowledgebaseRepository = $knowledgebaseRepository;
        $this->knowledgebaseRoute = $knowledgebaseRoute;
        $this->sectionInserter = $sectionInserter;
        $this->sectionValidator = $sectionValidator;
        $this->urlIdGenerator = $urlIdGenerator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): array
    {
        $knowledgebase = $this->getKnowledgebase($parameters, $person->getOrganizationId());
        $this->sectionValidator->validate($parameters, $knowledgebase, true);

        $id = $this->idGenerator->generate();
        $urlSlug = $this->urlSlugGenerator->generateFromString($parameters['name']);
        $urlId = $this->urlIdGenerator->generate();

        $this->connection->beginTransaction();
        try {
            $result = $this->sectionInserter->insert(
                $id,
                $parameters['name'],
                $urlSlug,
                $urlId,
                $parameters['knowledgebase_id'],
                $person->getOrganizationId(),
                $parameters['description'] ?? null
            );

            $model = SectionModel::createFromArray($result);

            $route = $this->knowledgebaseRoute->create(
                $model,
                $model->getUrlSlug(),
                true,
                true
            );

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $knowledgebaseOwnerModel = $this->knowledgebaseOwner->get($knowledgebase);

        return [$model, $route, $knowledgebaseOwnerModel];
    }

    private function getKnowledgebase(array $parameters, string $organizationId): ?KnowledgebaseModel
    {
        if (!isset($parameters['knowledgebase_id'])) {
            return null;
        }

        $result = $this->knowledgebaseRepository->findById($parameters['knowledgebase_id'], $organizationId);
        if (null == $result) {
            return null;
        }

        return KnowledgebaseModel::createFromArray($result);
    }
}
