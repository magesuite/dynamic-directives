<?php

namespace MageSuite\DynamicDirectives\Model;

class Parser implements ParserInterface
{
    const DIRECTIVE_PATTERN = '/\{\{([a-zA-Z_]+)\s*([^}]+)*\}\}/si';

    const ATTRIBUTE_PATTERN = '/\w+=\".*?\"/';

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
        $argumentsString = $this->escapeSpecialCharsFromAttributesValues($argumentsString);
        $xml = new \SimpleXMLElement("<element $argumentsString/>");

        $arguments = [];

        foreach ($xml->attributes() as $name => $value) {
            $arguments[$name] = (string)$value[0];
        }

        return $arguments;
    }

    protected function escapeSpecialCharsFromAttributesValues($string)
    {
        $escapedString = '';

        preg_match_all(self::ATTRIBUTE_PATTERN, $string, $attributes);
        foreach($attributes[0] as $key => $attribute) {
            list($attributeName, $attributeValue) = explode('=', $attribute);

            $escapedString = $this->getEscapedAttributeString($attributeName, $attributeValue);
            if (!$this->isTheLastElement($key, $attributes[0])) {
                $escapedString .= ' ';
            }
        }

        return $escapedString;
    }

    protected function getEscapedAttributeString($attributeName, $attributeValue)
    {
        $escapedValue = htmlentities(trim($attributeValue, '"'));
        return $attributeName . '="' . $escapedValue . '"';
    }

    protected function isTheLastElement($key, $array)
    {
        $lastElementKey = count($array) -1;
        return $key == $lastElementKey;
    }

    public function getDirectivePattern()
    {
        return self::DIRECTIVE_PATTERN;
    }
}
