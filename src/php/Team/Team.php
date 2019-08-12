<?php
declare(strict_types=1);

namespace Hipper\Team;

use Doctrine\DBAL\Connection;
use Hipper\IdGenerator\IdGenerator;
use Hipper\Knowledgebase\Knowledgebase;
use Hipper\Person\PersonModel;
use Hipper\Url\UrlSlugGenerator;

class Team
{
    private $connection;
    private $idGenerator;
    private $knowledgebase;
    private $personToTeamMapInserter;
    private $teamInserter;
    private $teamMetadataInserter;
    private $teamModelMapper;
    private $teamValidator;
    private $urlSlugGenerator;

    public function __construct(
        Connection $connection,
        IdGenerator $idGenerator,
        Knowledgebase $knowledgebase,
        PersonToTeamMapInserter $personToTeamMapInserter,
        TeamInserter $teamInserter,
        TeamMetadataInserter $teamMetadataInserter,
        TeamModelMapper $teamModelMapper,
        TeamValidator $teamValidator,
        UrlSlugGenerator $urlSlugGenerator
    ) {
        $this->connection = $connection;
        $this->idGenerator = $idGenerator;
        $this->knowledgebase = $knowledgebase;
        $this->personToTeamMapInserter = $personToTeamMapInserter;
        $this->teamInserter = $teamInserter;
        $this->teamMetadataInserter = $teamMetadataInserter;
        $this->teamModelMapper = $teamModelMapper;
        $this->teamValidator = $teamValidator;
        $this->urlSlugGenerator = $urlSlugGenerator;
    }

    public function create(PersonModel $person, array $parameters): TeamModel
    {
        $this->teamValidator->validate($parameters, $person->getOrganizationId(), true);

        $id = $this->idGenerator->generate();
        $urlId = $this->urlSlugGenerator->generateFromString($parameters['name']);

        $this->connection->beginTransaction();
        try {
            $knowledgebase = $this->knowledgebase->create($person->getOrganizationId());
            $team = $this->teamInserter->insert(
                $id,
                $parameters['name'],
                $parameters['description'],
                $urlId,
                $knowledgebase['id'],
                $person->getOrganizationId()
            );
            $this->createMetadata($team['id']);
            $this->createPersonTeamMap($person->getId(), $team['id']);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

        return $this->teamModelMapper->createFromArray($team);
    }

    private function createMetadata(string $teamId): void
    {
        $id = $this->idGenerator->generate();
        $this->teamMetadataInserter->insert($id, $teamId);
    }

    private function createPersonTeamMap(string $personId, string $teamId): void
    {
        $id = $this->idGenerator->generate();
        $this->personToTeamMapInserter->insert($id, $personId, $teamId);
    }
}
