<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\ModelMapper;

class PersonModelMapper
{
    private $modelMapper;
    private $fields = [
        'id' => 'id',
        'name' => 'name',
        'abbreviated_name' => 'abbreviatedName',
        'email_address' => 'emailAddress',
        'email_address_verified' => 'emailAddressVerified',
        'onboarding_completed' => 'onboardingCompleted',
        'organization_id' => 'organizationId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    public function __construct(
        ModelMapper $modelMapper
    ) {
        $this->modelMapper = $modelMapper;
    }

    public function createFromArray(array $properties): PersonModel
    {
        $model = new PersonModel;
        $this->modelMapper->mapProperties($model, $this->fields, $properties);
        return $model;
    }
}
