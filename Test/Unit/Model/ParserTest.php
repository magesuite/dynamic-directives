<?php

namespace MageSuite\DynamicDirectives\Test\Unit\Model;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\DynamicDirectives\Model\Parser
     */
    protected $parser;

    public function setUp(): void
    {
        $directivePool = $this->createStub(\MageSuite\DynamicDirectives\Model\Pool::class);
        $directivePool->method('getDefinedDirectivesIdentifiers')->willReturn(['sample']);
        $directivePool->method('create')->willReturnCallback(function () {
            return new \MageSuite\DynamicDirectives\Model\Directives\Sample();
        });

        $this->parser = new \MageSuite\DynamicDirectives\Model\Parser($directivePool);
    }

    /**
     * @dataProvider textsWithDirectives
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('textsWithDirectives')]
    public function testItParsesDirectives($text, $expectedDirectives)
    {
        $directives = $this->parser->getDirectives($text);

        $this->assertCount(count($expectedDirectives), $directives);

        foreach ($expectedDirectives as $expectedDirective) {
            $directive = array_shift($directives);
            $arguments = $directive->getArguments();

            $this->assertEquals($expectedDirective['identifier'], $directive->getIdentifier());
            $this->assertCount(count($expectedDirective['arguments']), $directive->getArguments());

            foreach ($expectedDirective['arguments'] as $key => $value) {
                $this->assertEquals($value, $arguments[$key]);
            }
        }
    }

    public static function textsWithDirectives(): array
    {
        return [
            [
                '{{sample argument1="value1" argument2="value2"}} some text ' . PHP_EOL . ' inside {{sample some="argument"}}',
                [
                    [
                        'identifier' => 'sample',
                        'arguments' => [
                            'argument1' => 'value1',
                            'argument2' => 'value2',
                        ]
                    ],
                    [
                        'identifier' => 'sample',
                        'arguments' => [
                            'some' => 'argument',
                        ]
                    ]
                ]
            ],
            [
                '{{sample argument1="value1" argument2="value2"}}',
                [
                    [
                        'identifier' => 'sample',
                        'arguments' => [
                            'argument1' => 'value1',
                            'argument2' => 'value2',
                        ]
                    ]
                ]
            ],
            [
                '{{sample argument1="value1" argument2="value2" argument3="\'&><"}}',
                [
                    [
                        'identifier' => 'sample',
                        'arguments' => [
                            'argument1' => 'value1',
                            'argument2' => 'value2',
                            'argument3' => '\'&><',
                        ]
                    ]
                ]
            ]
        ];
    }
}
