<?php

namespace MageSuite\DynamicDirectives\Test\Unit\Model;

class ParserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\DynamicDirectives\Model\Parser
     */
    protected $parser;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->parser = $this->objectManager->create(\MageSuite\DynamicDirectives\Model\Parser::class);
    }

    /**
     * @dataProvider textsWithDirectives
     */
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

    public static function textsWithDirectives()
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
            ]
        ];
    }
}
