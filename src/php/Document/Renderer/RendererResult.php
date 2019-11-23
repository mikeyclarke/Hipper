<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

class RendererResult
{
    private $outline;
    private $content = '';

    public function setOutline(array $outline): void
    {
        $this->outline = $outline;
    }

    public function getOutline(): ?array
    {
        return $this->outline;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}
