<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Mark;

use Hipper\Document\Renderer\Exception\InvalidMarkException;
use Hipper\Document\Renderer\HtmlFragmentRendererContext;
use Hipper\Document\Renderer\Mark\MarkInterface;

class MarkFactory
{
    const MARK_CLASS_MAP = [
        'code' => Code::class,
        'emphasis' => Emphasis::class,
        'link' => Link::class,
        'strike' => Strike::class,
        'strong' => Strong::class,
    ];

    public function create(string $name, HtmlFragmentRendererContext $context): MarkInterface
    {
        if (!isset(self::MARK_CLASS_MAP[$name])) {
            throw new InvalidMarkException;
        }

        $class = self::MARK_CLASS_MAP[$name];
        return new $class($context);
    }
}
