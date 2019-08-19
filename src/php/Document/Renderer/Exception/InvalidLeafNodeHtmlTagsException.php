<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Exception;

class InvalidLeafNodeHtmlTagsException extends \Exception
{
    protected $message = 'Leaf nodes should render as empty HTML elements with self-closing tags';
}
