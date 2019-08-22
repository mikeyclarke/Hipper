<?php
declare(strict_types=1);

namespace Hipper\Project;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\Knowledgebase;
use Hipper\Person\PersonModel;
use Hipper\Url\UrlSlugGenerator;

class Project
{
    private $connection;
    private $idGenerator;
    private $knowledgebase;
    private $personToProjectMapInserter;
    private $projectInserter;
    private $projectValidator;
    private $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        IdGenerator $idGenerator,
        Knowledgebase $knowledgebase,
        PersonToProjectMapInserter $personToProjectMapInserter,
        ProjectInserter $projectInserter,
        ProjectValidator $projectValidator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->idGenerator = $idGenerator;
        $this->knowledgebase = $knowledgebase;
        $this->personToProjectMapInserter = $personToProjectMapInserter;
        $this->projectInserter = $projectInserter;
        $this->projectValidator = $projectValidator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): ProjectModel
    {
        $organizationId = $person->getOrganizationId();

        $this->projectValidator->validate($parameters, $organizationId, true);

        $id = $this->idGenerator->generate();
        $urlId = $this->urlSlugGenerator->generateFromString($parameters['name']);

        $this->connection->beginTransaction();
        try {
            $knowledgebase = $this->knowledgebase->create('project', $organizationId);
            $projectResult = $this->projectInserter->insert(
                $id,
                $parameters['name'],
                $parameters['description'] ?? null,
                $urlId,
                $knowledgebase['id'],
                $organizationId
            );
            $this->createPersonToProjectMap($person->getId(), $projectResult['id']);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        $project = ProjectModel::createFromArray($projectResult);
        return $project;
    }

    private function createPersonToProjectMap(string $personId, string $projectId): void
    {
        $id = $this->idGenerator->generate();
        $this->personToProjectMapInserter->insert($id, $personId, $projectId);
    }
}
