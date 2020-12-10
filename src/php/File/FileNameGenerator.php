<?php
declare(strict_types=1);

namespace Hipper\File;

use Ausi\SlugGenerator\SlugGenerator;

class FileNameGenerator
{
    /* Restricted characters and Windows reserved names taken from https://github.com/madrobby/zaru */

    private const DEFAULT_FILE_NAME = 'File';
    private const MAX_LENGTH = 255;
    private const RESTRICTED_CHARACTERS = "/?*:|\"<>.\\\\";
    private const VALID_CHARACTER_GROUPS = "\p{N}\p{L}\p{S}\p{P} ";
    private const WINDOWS_RESERVED_NAMES = [
        'CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'COM3', 'COM4', 'COM5', 'COM6', 'COM7', 'COM8', 'COM9', 'LPT1',
        'LPT2', 'LPT3', 'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9',
    ];

    public function generateFromString(
        string $stringToSluggify,
        string $extension = null,
        string $defaultName = null
    ): string {
        $generator = new SlugGenerator;
        $fileName = $this->sluggify($generator, $stringToSluggify);

        $fileName = $this->removeReservedNames($fileName);
        $fileName = $this->stripTrailingSpaces($fileName);
        $fileName = $this->stripRedundantWhitespace($fileName);

        $maxLength = self::MAX_LENGTH;
        if (null !== $extension) {
            $maxLength -= (mb_strlen($extension) + 1);
        }

        if (mb_strlen($fileName) > $maxLength) {
            $fileName = $this->trimToFit($fileName, $maxLength);
        }

        if (empty($fileName)) {
            $fileName = (null !== $defaultName) ? $defaultName : self::DEFAULT_FILE_NAME;
        }

        if (null === $extension) {
            return $fileName;
        }

        return sprintf('%s.%s', $fileName, $extension);
    }

    public function generateRandom(string $extension = null): string
    {
        if (null === $extension) {
            return uniqid();
        }

        return sprintf('%s.%s', uniqid(), $extension);
    }

    private function sluggify(SlugGenerator $generator, string $string): string
    {
        $options = [
            'delimiter' => ' ',
            'validChars' => self::VALID_CHARACTER_GROUPS,
            'ignoreChars' => self::RESTRICTED_CHARACTERS,
        ];
        return $generator->generate($string, $options);
    }

    private function removeReservedNames(string $fileName): string
    {
        $upper = mb_strtoupper($fileName);
        if (in_array($upper, self::WINDOWS_RESERVED_NAMES)) {
            return '';
        }
        return $fileName;
    }

    private function stripTrailingSpaces(string $fileName): string
    {
        return trim($fileName);
    }

    private function stripRedundantWhitespace(string $fileName): string
    {
        return preg_replace('/ +/', ' ', $fileName);
    }

    private function trimToFit(string $fileName, int $maxLength): string
    {
        return mb_strimwidth($fileName, 0, $maxLength);
    }
}
