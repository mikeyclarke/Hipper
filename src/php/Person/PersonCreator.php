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
    private $encoderFactory;
    private $idGenerator;

    public function __construct(
        PersonInserter $personInserter,
        PersonMetadataInserter $personMetadataInserter,
        PersonModelMapper $personModelMapper,
        PersonPasswordEncoderFactory $encoderFactory,
        IdGenerator $idGenerator
    ) {
        $this->personInserter = $personInserter;
        $this->personMetadataInserter = $personMetadataInserter;
        $this->personModelMapper = $personModelMapper;
        $this->encoderFactory = $encoderFactory;
        $this->idGenerator = $idGenerator;
    }

    public function create(
        OrganizationModel $organization,
        string $name,
        string $emailAddress,
        string $rawPassword,
        bool $emailAddressVerified = false
    ): array {
        $encoder = $this->encoderFactory->create();
        $person = $this->personInserter->insert(
            $this->idGenerator->generate(),
            $name,
            $emailAddress,
            $encoder->encodePassword($rawPassword, null),
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
