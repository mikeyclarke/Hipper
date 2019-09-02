<?Php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

use Hipper\ModelTrait;

class KnowledgebaseRouteModel
{
    use ModelTrait;

    const URL_SEGMENT_JOIN_CHAR = '~';
    const FIELD_MAP = [
        'id' => 'id',
        'url_id' => 'urlId',
        'route' => 'route',
        'is_canonical' => 'isCanonical',
        'entity' => 'entity',
        'organization_id' => 'organizationId',
        'knowledgebase_id' => 'knowledgebaseId',
        'section_id' => 'sectionId',
        'document_id' => 'documentId',
        'created' => 'created',
        'updated' => 'updated',
    ];

    private $id;
    private $urlId;
    private $route;
    private $isCanonical;
    private $entity;
    private $organizationId;
    private $knowledgebaseId;
    private $sectionId;
    private $documentId;
    private $created;
    private $updated;

    public static function createFromArray(array $array): KnowledgebaseRouteModel
    {
        $model = new static;
        $model->mapProperties($array);
        return $model;
    }

    public function toUrlSegment(): string
    {
        return sprintf(
            '%s%s%s',
            $this->route,
            self::URL_SEGMENT_JOIN_CHAR,
            $this->urlId
        );
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setUrlId(string $urlId): void
    {
        $this->urlId = $urlId;
    }

    public function getUrlId(): string
    {
        return $this->urlId;
    }

    public function setRoute(string $route): void
    {
        $this->route = $route;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function setIsCanonical(bool $isCanonical): void
    {
        $this->isCanonical = $isCanonical;
    }

    public function getIsCanonical(): bool
    {
        return $this->isCanonical;
    }

    public function isCanonical(): bool
    {
        return $this->isCanonical;
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

    public function setKnowledgebaseId(string $knowledgebaseId): void
    {
        $this->knowledgebaseId = $knowledgebaseId;
    }

    public function getKnowledgebaseId(): string
    {
        return $this->knowledgebaseId;
    }

    public function setSectionId(?string $sectionId): void
    {
        $this->sectionId = $sectionId;
    }

    public function getSectionId(): ?string
    {
        return $this->sectionId;
    }

    public function setDocumentId(?string $documentId): void
    {
        $this->documentId = $documentId;
    }

    public function getDocumentId(): ?string
    {
        return $this->documentId;
    }

    public function setCreated(string $created): void
    {
        $this->created = $created;
    }

    public function getCreated(): string
    {
        return $this->created;
    }

    public function setUpdated(string $updated): void
    {
        $this->updated = $updated;
    }

    public function getUpdated(): string
    {
        return $this->updated;
    }
}
