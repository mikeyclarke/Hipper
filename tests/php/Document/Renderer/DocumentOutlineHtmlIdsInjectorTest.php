<?php
declare(strict_types=1);

namespace Hipper\Document\Renderer;

use Hipper\Document\Renderer\DocumentOutlineHtmlIdsInjector;
use Hipper\Url\UrlSlugGenerator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class DocumentOutlineHtmlIdsInjectorTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    private $urlSlugGenerator;
    private $injector;

    public function setUp(): void
    {
        $this->urlSlugGenerator = m::mock(UrlSlugGenerator::class);
        $this->injector = new DocumentOutlineHtmlIdsInjector(
            $this->urlSlugGenerator
        );
    }

    /**
     * @test
     */
    public function inject()
    {
        $doc = [
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'I am not a heading',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'First heading',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Another heading',
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Also not a heading',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Last heading',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'content' => [
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'I am not a heading',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'First heading',
                        ],
                    ],
                    'html_id' => '_first-heading',
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Another heading',
                        ],
                    ],
                    'html_id' => '_another-heading',
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Also not a heading',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Last heading',
                        ],
                    ],
                    'html_id' => '_last-heading',
                ],
            ],
        ];

        $this->createUrlSlugGeneratorExpectation(['First heading'], 'first-heading');
        $this->createUrlSlugGeneratorExpectation(['Another heading'], 'another-heading');
        $this->createUrlSlugGeneratorExpectation(['Last heading'], 'last-heading');

        $result = $this->injector->inject($doc);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function textFromMultipleTextNodesIsConcatinated()
    {
        $doc = [
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'A ',
                        ],
                        [
                            'type' => 'text',
                            'text' => 'code mark',
                            'marks' => [
                                [
                                    'type' => 'code',
                                ]
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => ' in a heading',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'content' => [
                [
                    'type' => 'heading',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'A ',
                        ],
                        [
                            'type' => 'text',
                            'text' => 'code mark',
                            'marks' => [
                                [
                                    'type' => 'code',
                                ]
                            ],
                        ],
                        [
                            'type' => 'text',
                            'text' => ' in a heading',
                        ],
                    ],
                    'html_id' => '_a-code-mark-in-a-heading',
                ],
            ],
        ];

        $this->createUrlSlugGeneratorExpectation(['A code mark in a heading'], 'a-code-mark-in-a-heading');

        $result = $this->injector->inject($doc);
        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     */
    public function duplicateIdsArePreventedWithAnIncrementer()
    {
        $doc = [
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'First topic',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Introduction',
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some topic 1 intro text',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Second topic',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Introduction',
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some topic 2 intro text',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Third topic',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Introduction',
                        ],
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some topic 3 intro text',
                        ],
                    ],
                ],
            ],
        ];

        $expected = [
            'content' => [
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'First topic',
                        ],
                    ],
                    'html_id' => '_first-topic',
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Introduction',
                        ],
                    ],
                    'html_id' => '_introduction',
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some topic 1 intro text',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Second topic',
                        ],
                    ],
                    'html_id' => '_second-topic',
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Introduction',
                        ],
                    ],
                    'html_id' => '_introduction--1',
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some topic 2 intro text',
                        ],
                    ],
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 1,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Third topic',
                        ],
                    ],
                    'html_id' => '_third-topic',
                ],
                [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => 2,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Introduction',
                        ],
                    ],
                    'html_id' => '_introduction--2',
                ],
                [
                    'type' => 'paragraph',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'Some topic 3 intro text',
                        ],
                    ],
                ],
            ],
        ];

        $this->createUrlSlugGeneratorExpectation(['First topic'], 'first-topic');
        $this->createUrlSlugGeneratorExpectation(['Introduction'], 'introduction');
        $this->createUrlSlugGeneratorExpectation(['Second topic'], 'second-topic');
        $this->createUrlSlugGeneratorExpectation(['Introduction'], 'introduction');
        $this->createUrlSlugGeneratorExpectation(['Third topic'], 'third-topic');
        $this->createUrlSlugGeneratorExpectation(['Introduction'], 'introduction');

        $result = $this->injector->inject($doc);
        $this->assertEquals($expected, $result);
    }

    private function createUrlSlugGeneratorExpectation($args, $result)
    {
        $this->urlSlugGenerator
            ->shouldReceive('generateFromString')
            ->once()
            ->with(...$args)
            ->andReturn($result);
    }
}
