<?php
declare(strict_types=1);

namespace Lithos\Url;

use Ausi\SlugGenerator\SlugGenerator;
use Lithos\Url\AusiSlugGeneratorFactory;
use Locale;

class UrlSlugGenerator
{
    const MAX_LENGTH = 100;

    private $slugGeneratorFactory;

    public function __construct(
        AusiSlugGeneratorFactory $slugGeneratorFactory
    ) {
        $this->slugGeneratorFactory = $slugGeneratorFactory;
    }

    public function generateFromString(string $stringToSluggify): string
    {
        $generator = $this->slugGeneratorFactory->create();
        $slug = $this->sluggify($generator, $stringToSluggify);

        if (empty($slug)) {
            $slug = $this->sluggify($generator, $stringToSluggify, true);
            $slug = $this->replaceRegionIndicators($slug);
        }

        if (mb_strlen($slug) > self::MAX_LENGTH) {
            $slug = $this->trimToFit($slug);
        }

        if (empty($slug)) {
            return 'untitled';
        }

        return $slug;
    }

    private function sluggify(SlugGenerator $generator, string $string, bool $transliterateEmoji = false): string
    {
        $options = [];
        if ($transliterateEmoji) {
            $options['preTransforms'] = ['Name', "\\\ { 'N' > "];
            $options['ignoreChars'] = "\p{Mn}\p{Lm}\p{Sk}\x{200D}";
        }
        return $generator->generate($string, $options);
    }

    private function replaceRegionIndicators(string $slug): string
    {
        return preg_replace_callback(
            '/regional-indicator-symbol-letter-([a-z])-regional-indicator-symbol-letter-([a-z])/',
            function ($matches) {
                return mb_strtolower(
                    preg_replace(
                        '/\s/',
                        '-',
                        Locale::getDisplayRegion(sprintf('-%s%s', $matches[1], $matches[2]))
                    )
                );
            },
            $slug
        );
    }

    private function trimToFit(string $slug): string
    {
        $slug = mb_strimwidth($slug, 0, self::MAX_LENGTH);
        $slug = trim($slug, '-');
        return $slug;
    }
}
