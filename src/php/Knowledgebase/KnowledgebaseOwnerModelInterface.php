<?Php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

interface KnowledgebaseOwnerModelInterface
{
    public function getName(): string;

    public function getUrlId(): ?string;

    public function getKnowledgebaseId(): string;
}
