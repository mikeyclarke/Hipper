<?php
declare(strict_types=1);

namespace Hipper\Organization;

use Hipper\ModelMapper;

class OrganizationModelMapper
{
    private $modelMapper;
    private $fields = [
        'id' => 'id',
        'name' => 'name',
        'subdomain' => 'subdomain',
        'approved_email_domain_signup_allowed' => 'approvedEmailDomainSignupAllowed',
        'approved_email_domains' => 'approvedEmailDomains',
        'created' => 'created',
        'updated' => 'updated',
    ];

    public function __construct(
        ModelMapper $modelMapper
    ) {
        $this->modelMapper = $modelMapper;
    }

    public function createFromArray(array $properties): OrganizationModel
    {
        $model = new OrganizationModel;
        $this->modelMapper->mapProperties($model, $this->fields, $properties);
        return $model;
    }
}
