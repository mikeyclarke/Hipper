<?php
declare(strict_types=1);

namespace Lithos\Team;

use Ausi\SlugGenerator\SlugGenerator;
use Lithos\IdGenerator\IdGenerator;
use Lithos\Knowledgebase\Knowledgebase;
use Lithos\Person\PersonModel;

class Team
{
    private $idGenerator;
    private $knowledgebase;
    private $personToTeamMapInserter;
    private $teamInserter;
    private $teamMetadataInserter;
    private $teamValidator;

    public function __construct(
        IdGenerator $idGenerator,
        Knowledgebase $knowledgebase,
        PersonToTeamMapInserter $personToTeamMapInserter,
        TeamInserter $teamInserter,
        TeamMetadataInserter $teamMetadataInserter,
        TeamValidator $teamValidator
    ) {
        $this->idGenerator = $idGenerator;
        $this->knowledgebase = $knowledgebase;
        $this->personToTeamMapInserter = $personToTeamMapInserter;
        $this->teamInserter = $teamInserter;
        $this->teamMetadataInserter = $teamMetadataInserter;
        $this->teamValidator = $teamValidator;
    }

    public function create(PersonModel $person, array $parameters): void
    {
        $this->teamValidator->validate($parameters, $person->getOrganizationId(), true);

        $id = $this->idGenerator->generate();
        $urlId = $this->generateUrlId($parameters['name']);
        $knowledgebase = $this->knowledgebase->create($person->getOrganizationId());

        $team = $this->teamInserter->insert(
            $id,
            $parameters['name'],
            $urlId,
            $knowledgebase['id'],
            $person->getOrganizationId()
        );

        $this->createMetadata($team['id']);
        $this->createPersonTeamMap($person->getId(), $team['id']);
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

    private function generateUrlId(string $name): string
    {
        $generator = new SlugGenerator;
        return $generator->generate($name);
    }
}
