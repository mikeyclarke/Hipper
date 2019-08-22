<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\IdGenerator\IdGenerator;
use Hipper\Organization\OrganizationModel;

class PersonCreator
{
    private $personInserter;
    private $passwordEncoder;
    private $idGenerator;

    public function __construct(
        PersonInserter $personInserter,
        PersonPasswordEncoder $passwordEncoder,
        IdGenerator $idGenerator
    ) {
        $this->personInserter = $personInserter;
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
        $abbreviatedName = $this->getAbbreviatedName($name);
        $person = $this->personInserter->insert(
            $this->idGenerator->generate(),
            $name,
            $abbreviatedName,
            $emailAddress,
            $this->passwordEncoder->encodePassword($rawPassword),
            $organization->getId(),
            $emailAddressVerified
        );

        $model = PersonModel::createFromArray($person);

        // TODO: If email address verified send welcome email

        return [$model, $person['password']];
    }

    private function getAbbreviatedName(string $name): string
    {
        $nameRepresentation = new NameRepresentation($name);
        return $nameRepresentation->abbreviated();
    }
}
