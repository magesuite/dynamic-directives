<?php

namespace MageSuite\DynamicDirectives\Test\Unit\Service;

class DirectiveApplierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\MageSuite\DynamicDirectives\Model\Parser
     */
    protected $directiveParserStub;

    /**
     * @var \MageSuite\DynamicDirectives\Service\DirectiveApplier
     */
    protected $directiveApplier;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->directiveParserStub = $this
            ->getMockBuilder(\MageSuite\DynamicDirectives\Model\Parser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directiveApplier = $this->objectManager->create(
            \MageSuite\DynamicDirectives\Service\DirectiveApplier::class,
            ['directiveParser' => $this->directiveParserStub]
        );
    }

    public function testItReplacesDirectivesWithTheirValues()
    {
        $text = '{{sample argument1="value1"}}' . PHP_EOL . ' some text ' . PHP_EOL . '{{sample argument2="value2"}}';

        $this->directiveParserStub->method('getDirectives')->willReturn([
            $this->createDirectiveStub('{{sample argument1="value1"}}', 'first_directive_value'),
            $this->createDirectiveStub('{{sample argument2="value2"}}', 'second_directive_value'),
        ]);

        $textAfterDirectivesWereApplied = $this->directiveApplier->apply($text);

        $this->assertEquals('first_directive_value' . PHP_EOL . ' some text ' . PHP_EOL . 'second_directive_value', $textAfterDirectivesWereApplied);
    }

    public function testItReplacesDirectivesWithEmptyTextWhenExceptionIsThrown()
    {
        $text = '{{sample argument1="value1"}}' . PHP_EOL . 'some text';

        $this->directiveParserStub->method('getDirectives')->willReturn([
            $this->createDirectiveStub('{{sample argument1="value1"}}', 'second_directive_value', true),
        ]);

        $textAfterDirectivesWereApplied = $this->directiveApplier->apply($text);

        $this->assertEquals('' . PHP_EOL . 'some text', $textAfterDirectivesWereApplied);
    }

    protected function createDirectiveStub(string $originalValue, string $value, $exceptionThrown = false)
    {
        $directiveStub = $this->getMockBuilder(\MageSuite\DynamicDirectives\Model\DirectiveInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($exceptionThrown) {
            $directiveStub->method('getValue')->willThrowException(new \Exception('Generic exception'));
        } else {
            $directiveStub->method('getValue')->willReturn($value);
        }

        $directiveStub->method('getOriginalValue')->willReturn($originalValue);
        $directiveStub->method('getIdentities')->willReturn([]);

        return $directiveStub;
    }
}
