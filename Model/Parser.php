<?php

namespace MageSuite\DynamicDirectives\Model;

class Parser implements ParserInterface
{
    const DIRECTIVE_PATTERN = '/\{\{([a-zA-Z_]+)\s*([^}]+)*\}\}/si';

    /**
     * @var \MageSuite\DynamicDirectives\Model\Pool
     */
    protected $directivePool;

    public function __construct(\MageSuite\DynamicDirectives\Model\Pool $directivePool)
    {
        $this->directivePool = $directivePool;
    }

    /**
     * @param $text
     * @return Directive[]
     */
    public function getDirectives($text)
    {
        $definedDirectivesIdentifiers = $this->directivePool->getDefinedDirectivesIdentifiers();

        preg_match_all($this->getDirectivePattern(), $text, $directives, PREG_SET_ORDER);

        if (empty($directives)) {
            return [];
        }

        $parsedDirectives = [];

        foreach ($directives as $directive) {
            $originalValue = $directive[0];
            $identifier = $directive[1];
            $arguments = isset($directive[2]) ? $this->parseArguments($directive[2]) : [];

            if (!in_array($identifier, $definedDirectivesIdentifiers)) {
                continue;
            }

            /** @var Directive $directive */
            $directive = $this->directivePool->create($identifier);

            $directive->setOriginalValue($originalValue);
            $directive->setIdentifier($identifier);
            $directive->setArguments($arguments);

            $parsedDirectives[] = $directive;
        }

        return $parsedDirectives;
    }

    private function parseArguments($argumentsString)
    {
        $argumentsString = $this->escapeXml($argumentsString);
        $xml = new \SimpleXMLElement("<element $argumentsString/>");

        $arguments = [];

        foreach ($xml->attributes() as $name => $value) {
            $arguments[$name] = (string)$value[0];
        }

        return $arguments;
    }

    protected function escapeXml($string)
    {
        return str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $string);
    }

    public function getDirectivePattern()
    {
        return self::DIRECTIVE_PATTERN;
    }
}
