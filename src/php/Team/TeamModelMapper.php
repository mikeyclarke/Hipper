<?php
declare(strict_types=1);

namespace Hipper\Team;

use Hipper\ModelMapper;

class TeamModelMapper
{
    private $modelMapper;
    private $fields = [
        'id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'url_id' => 'urlId',
        'knowledgebase_id' => 'knowledgebaseId',
        'organization_id' => 'organizationId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    public function __construct(
        ModelMapper $modelMapper
    ) {
        $this->modelMapper = $modelMapper;
    }

    public function createFromArray(array $properties): TeamModel
    {
        $model = new TeamModel;
        $this->modelMapper->mapProperties($model, $this->fields, $properties);
        return $model;
    }
}
