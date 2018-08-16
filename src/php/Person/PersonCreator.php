<?php
namespace hleo\Person;

use hleo\EmailAddressVerification\RequestEmailAddressVerification;
use hleo\IdGenerator\IdGenerator;
use hleo\Organization\OrganizationCreator;

class PersonCreator
{
    private $personInserter;
    private $personModelMapper;
    private $encoderFactory;
    private $idGenerator;
    private $organizationCreator;
    private $requestEmailAddressVerification;

    public function __construct(
        PersonInserter $personInserter,
        PersonModelMapper $personModelMapper,
        PersonPasswordEncoderFactory $encoderFactory,
        IdGenerator $idGenerator,
        OrganizationCreator $organizationCreator,
        RequestEmailAddressVerification $requestEmailAddressVerification
    ) {
        $this->personInserter = $personInserter;
        $this->personModelMapper = $personModelMapper;
        $this->encoderFactory = $encoderFactory;
        $this->idGenerator = $idGenerator;
        $this->organizationCreator = $organizationCreator;
        $this->requestEmailAddressVerification = $requestEmailAddressVerification;
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

        $model = $this->personModelMapper->createFromArray($person);
        $this->requestEmailAddressVerification->sendVerificationRequest($model);

        return $model;
    }
}
