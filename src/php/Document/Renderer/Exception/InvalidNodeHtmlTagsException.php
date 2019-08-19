<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer\Exception;

class InvalidNodeHtmlTagsException extends \Exception
{
    protected $message = 'Non-leaf nodes should render as non-empty HTML elements with an opening and closing tag';
}
