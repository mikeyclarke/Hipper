<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

use Hipper\Document\Renderer\Exception\ContentDecodeException;
use JsonException;

class Decoder
{
    public function decode($doc): array
    {
        try {
            $decoded = json_decode($doc, true, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new ContentDecodeException;
        }

        if ($this->isInvalidDocFormat($decoded)) {
            throw new ContentDecodeException;
        }

        return $decoded;
    }

    private function isInvalidDocFormat($doc): bool
    {
        if (!is_array($doc)) {
            return true;
        }

        if (!isset($doc['type']) || $doc['type'] !== 'doc') {
            return true;
        }

        if (!isset($doc['content'])) {
            return true;
        }

        return false;
    }
}
