<?php
declare(strict_types=1);

namespace Lithos\Tests\Url;

use Ausi\SlugGenerator\SlugGenerator;
use Lithos\Url\AusiSlugGeneratorFactory;
use Lithos\Url\UrlSlugGenerator;
use Locale;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class UrlSlugGeneratorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    const EMOJI_TRANSLITERATE_OPTIONS = [
        'preTransforms' => ['Name', "\\\ { 'N' > "],
        'ignoreChars' => "\p{Mn}\p{Lm}\p{Sk}\x{200D}",
    ];

    private $generatorFactory;
    private $urlSlugGenerator;
    private $slugGenerator;

    public function setUp(): void
    {
        $this->generatorFactory = m::mock(AusiSlugGeneratorFactory::class);
        $this->urlSlugGenerator = new UrlSlugGenerator(
            $this->generatorFactory
        );

        $this->slugGenerator = m::mock(SlugGenerator::class);
    }

    /**
     * @test
     */
    public function generateFromString()
    {
        $stringToSluggify = 'Test string';
        $expected = 'test-string';

        $this->createAusiSlugGeneratorFactoryExpectation();
        $this->createSlugGeneratorExpectation([$stringToSluggify, []], $expected);

        $result = $this->urlSlugGenerator->generateFromString($stringToSluggify);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function retryWithEmojiTransliterationIfFirstResultIsEmpty()
    {
        $stringToSluggify = '👊🍾';
        $expected = 'fisted-hand-sign-bottle-with-popping-cork';

        $this->createAusiSlugGeneratorFactoryExpectation();
        $this->createSlugGeneratorExpectation([$stringToSluggify, []], '');
        $this->createSlugGeneratorExpectation([$stringToSluggify, self::EMOJI_TRANSLITERATE_OPTIONS], $expected);

        $result = $this->urlSlugGenerator->generateFromString($stringToSluggify);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function regionalIndicatorsAreManuallyConvertedToRegionNames()
    {
        $stringToSluggify = '👋🇺🇳';
        $flagDisplay = mb_strtolower(preg_replace('/\s/', '-', Locale::getDisplayRegion('-un')));
        $expected = sprintf('waving-hand-sign-%s', $flagDisplay);

        $generatorResult = 'waving-hand-sign-regional-indicator-symbol-letter-u-regional-indicator-symbol-letter-n';

        $this->createAusiSlugGeneratorFactoryExpectation();
        $this->createSlugGeneratorExpectation([$stringToSluggify, []], '');
        $this->createSlugGeneratorExpectation(
            [$stringToSluggify, self::EMOJI_TRANSLITERATE_OPTIONS],
            $generatorResult
        );

        $result = $this->urlSlugGenerator->generateFromString($stringToSluggify);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function emptyResultFromGenerator()
    {
        $stringToSluggify = '-----';
        $expected = 'untitled';

        $this->createAusiSlugGeneratorFactoryExpectation();
        $this->createSlugGeneratorExpectation([$stringToSluggify, []], '');
        $this->createSlugGeneratorExpectation([$stringToSluggify, self::EMOJI_TRANSLITERATE_OPTIONS], '');

        $result = $this->urlSlugGenerator->generateFromString($stringToSluggify);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function resultIsTrimmedToMaxLength()
    {
        $stringToSluggify =
            'This is a super long string that exceeds the max length of one hundred characters, but it’s not a problem'
        ;
        $expected =
            'this-is-a-super-long-string-that-exceeds-the-max-length-of-one-hundred-characters-but-it-s-not-a-pro';

        $this->createAusiSlugGeneratorFactoryExpectation();
        $this->createSlugGeneratorExpectation(
            [$stringToSluggify, []],
            'this-is-a-super-long-string-that-exceeds-the-max-length-of-one-hundred-characters-but-it-s-not-a-problem'
        );

        $result = $this->urlSlugGenerator->generateFromString($stringToSluggify);
        $this->assertEquals($expected, $result);
    }

    private function createSlugGeneratorExpectation($args, $result)
    {
        $this->slugGenerator
            ->shouldReceive('generate')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }

    private function createAusiSlugGeneratorFactoryExpectation()
    {
        $this->generatorFactory
            ->shouldReceive('create')
            ->once()
            ->andReturn($this->slugGenerator);
    }
}
