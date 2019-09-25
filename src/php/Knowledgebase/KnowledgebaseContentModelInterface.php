<?php
declare(strict_types=1);

namespace Hipper\Knowledgebase;

interface KnowledgebaseContentModelInterface
{
    public function getId(): string;

    public function getUrlId(): string;

    public function getKnowledgebaseId(): string;

    public function getOrganizationId(): string;
}
