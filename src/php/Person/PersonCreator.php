<?php
declare(strict_types=1);

namespace Lithos\Person;

use Lithos\IdGenerator\IdGenerator;
use Lithos\Organization\OrganizationModel;

class PersonCreator
{
    private $personInserter;
    private $personMetadataInserter;
    private $personModelMapper;
    private $passwordEncoder;
    private $idGenerator;

    public function __construct(
        PersonInserter $personInserter,
        PersonMetadataInserter $personMetadataInserter,
        PersonModelMapper $personModelMapper,
        PersonPasswordEncoder $passwordEncoder,
        IdGenerator $idGenerator
    ) {
        $this->personInserter = $personInserter;
        $this->personMetadataInserter = $personMetadataInserter;
        $this->personModelMapper = $personModelMapper;
        $this->passwordEncoder = $passwordEncoder;
        $this->idGenerator = $idGenerator;
    }

    public function create(
        OrganizationModel $organization,
        string $name,
        string $emailAddress,
        string $rawPassword,
        bool $emailAddressVerified = false
    ): array {
        $person = $this->personInserter->insert(
            $this->idGenerator->generate(),
            $name,
            $emailAddress,
            $this->passwordEncoder->encodePassword($rawPassword),
            $organization->getId(),
            $emailAddressVerified
        );
        $this->createPersonMetadata($person['id']);

        $model = $this->personModelMapper->createFromArray($person);

        // TODO: If email address verified send welcome email

        return [$model, $person['password']];
    }

    private function createPersonMetadata(string $personId): void
    {
        $id = $this->idGenerator->generate();
        $this->personMetadataInserter->insert($id, $personId);
    }
}
