<?php
namespace hleo\Person;

use hleo\ModelMapper;

class PersonModelMapper
{
    private $modelMapper;
    private $fields = [
        'id' => 'id',
        'name' => 'name',
        'email_address' => 'emailAddress',
        'role' => 'role',
        'email_address_verified' => 'emailAddressVerified',
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
