<?php
namespace Lithos\Person;

use Lithos\EmailAddressVerification\RequestEmailAddressVerification;
use Lithos\IdGenerator\IdGenerator;
use Lithos\Organization\OrganizationCreator;

class PersonCreator
{
    private $personInserter;
    private $personModelMapper;
    private $encoderFactory;
    private $personValidator;
    private $idGenerator;
    private $organizationCreator;
    private $requestEmailAddressVerification;

    public function __construct(
        PersonInserter $personInserter,
        PersonModelMapper $personModelMapper,
        PersonPasswordEncoderFactory $encoderFactory,
        PersonValidator $personValidator,
        IdGenerator $idGenerator,
        OrganizationCreator $organizationCreator,
        RequestEmailAddressVerification $requestEmailAddressVerification
    ) {
        $this->personInserter = $personInserter;
        $this->personModelMapper = $personModelMapper;
        $this->encoderFactory = $encoderFactory;
        $this->personValidator = $personValidator;
        $this->idGenerator = $idGenerator;
        $this->organizationCreator = $organizationCreator;
        $this->requestEmailAddressVerification = $requestEmailAddressVerification;
    }

    public function create(array $input)
    {
        $this->personValidator->validate($input, true);

        $organization = $this->organizationCreator->create();

        $encoder = $this->encoderFactory->create();
        $person = $this->personInserter->insert(
            $this->idGenerator->generate(),
            $input['name'],
            $input['email_address'],
            $encoder->encodePassword($input['password'], null),
            $organization['id'],
            'owner'
        );

        $model = $this->personModelMapper->createFromArray($person);
        $this->requestEmailAddressVerification->sendVerificationRequest($model);

        return $model;
    }
}
