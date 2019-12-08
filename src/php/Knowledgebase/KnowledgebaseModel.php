<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\ModelTrait;

final class KnowledgebaseModel
{
    use ModelTrait;

    const FIELD_MAP = [
        'id' => 'id',
        'entity' => 'entity',
        'organization_id' => 'organizationId',
        'created' => 'created',
    ];

    private $id;
    private $entity;
    private $organizationId;
    private $created;

    public static function createFromArray(array $array): KnowledgebaseModel
    {
        $model = new static;
        $model->mapProperties($array);
        return $model;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setEntity(string $entity): void
    {
        $this->entity = $entity;
    }

    public function getEntity(): string
    {
        return $this->entity;
    }

    public function setOrganizationId(string $organizationId): void
    {
        $this->organizationId = $organizationId;
    }

    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    public function setCreated(string $created): void
    {
        $this->created = $created;
    }

    public function getCreated(): string
    {
        return $this->created;
    }
}
