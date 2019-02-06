<?php
declare(strict_types=1);

namespace Lithos\Person;

use Lithos\EmailAddressVerification\RequestEmailAddressVerification;
use Lithos\IdGenerator\IdGenerator;
use Lithos\Organization\Organization;

class PersonCreator
{
    private $personInserter;
    private $personModelMapper;
    private $encoderFactory;
    private $personValidator;
    private $idGenerator;
    private $organization;
    private $requestEmailAddressVerification;

    public function __construct(
        PersonInserter $personInserter,
        PersonModelMapper $personModelMapper,
        PersonPasswordEncoderFactory $encoderFactory,
        PersonValidator $personValidator,
        IdGenerator $idGenerator,
        Organization $organization,
        RequestEmailAddressVerification $requestEmailAddressVerification
    ) {
        $this->personInserter = $personInserter;
        $this->personModelMapper = $personModelMapper;
        $this->encoderFactory = $encoderFactory;
        $this->personValidator = $personValidator;
        $this->idGenerator = $idGenerator;
        $this->organization = $organization;
        $this->requestEmailAddressVerification = $requestEmailAddressVerification;
    }

    public function create(array $input): array
    {
        $this->personValidator->validate($input, true);

        $organizationModel = $this->organization->create();

        $encoder = $this->encoderFactory->create();
        $person = $this->personInserter->insert(
            $this->idGenerator->generate(),
            $input['name'],
            $input['email_address'],
            $encoder->encodePassword($input['password'], null),
            $organizationModel->getId(),
            'owner'
        );

        $model = $this->personModelMapper->createFromArray($person);
        $this->requestEmailAddressVerification->sendVerificationRequest($model);

        return [$model, $person['password']];
    }
}
