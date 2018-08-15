<?php
namespace hleo\Person;

use hleo\IdGenerator\IdGenerator;
use hleo\Organization\OrganizationCreator;

class PersonCreator
{
    private $personInserter;
    private $encoderFactory;
    private $idGenerator;
    private $organizationCreator;

    public function __construct(
        PersonInserter $personInserter,
        PersonPasswordEncoderFactory $encoderFactory,
        IdGenerator $idGenerator,
        OrganizationCreator $organizationCreator
    ) {
        $this->personInserter = $personInserter;
        $this->encoderFactory = $encoderFactory;
        $this->idGenerator = $idGenerator;
        $this->organizationCreator = $organizationCreator;
    }

    public function create($name, $email, $password)
    {
        $organization = $this->organizationCreator->create();

        $encoder = $this->encoderFactory->create();
        $person = $this->personInserter->insert(
            $this->idGenerator->generate(),
            $name,
            $email,
            $encoder->encodePassword($password, null),
            $organization['id'],
            'owner'
        );

        return $person;
    }
}
