<?php
declare(strict_types=1);

namespace Lithos\Document;

use Lithos\ModelMapper;

class DocumentModelMapper
{
    private $modelMapper;
    private $fields = [
        'id' => 'id',
        'name' => 'name',
        'description' => 'description',
        'deduced_description' => 'deducedDescription',
        'content' => 'content',
        'url_slug' => 'urlSlug',
        'url_id' => 'urlId',
        'knowledgebase_id' => 'knowledgebaseId',
        'organization_id' => 'organizationId',
        'section_id' => 'sectionId',
        'created_by' => 'createdBy',
        'last_updated_by' => 'lastUpdatedBy',
        'created' => 'created',
        'updated' => 'updated',
    ];

    public function __construct(
        ModelMapper $modelMapper
    ) {
        $this->modelMapper = $modelMapper;
    }

    public function createFromArray(array $properties): DocumentModel
    {
        $model = new DocumentModel;
        $this->modelMapper->mapProperties($model, $this->fields, $properties);
        return $model;
    }
}
