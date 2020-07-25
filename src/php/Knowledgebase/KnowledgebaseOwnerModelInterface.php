<?Php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

interface KnowledgebaseOwnerModelInterface
{
    public function getId(): string;

    public function getName(): string;

    public function getUrlSlug(): ?string;

    public function getKnowledgebaseId(): string;
}
