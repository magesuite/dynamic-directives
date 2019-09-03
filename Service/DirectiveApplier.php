<?php

namespace MageSuite\DynamicDirectives\Service;

class DirectiveApplier
{
    /**
     * @var \MageSuite\DynamicDirectives\Model\Parser
     */
    protected $directiveParser;

    /**
     * @var array
     */
    protected $identities = [];

    public function __construct(\MageSuite\DynamicDirectives\Model\Parser $directiveParser)
    {
        $this->directiveParser = $directiveParser;
    }

    public function apply($text)
    {
        $directives = $this->directiveParser->getDirectives($text);

        if (empty($directives)) {
            return $text;
        }

        /** @var \MageSuite\DynamicDirectives\Model\DirectiveInterface $directive */
        foreach ($directives as $directive) {
            try {
                $text = str_replace($directive->getOriginalValue(), $directive->getValue(), $text);

                $this->identities = array_merge($this->identities, $directive->getIdentities());
            } catch (\Exception $e) {
                $text = str_replace($directive->getOriginalValue(), '', $text);
            }
        }

        return $text;
    }

    public function clearIdentities()
    {
        $this->identities = [];
    }

    public function getIdentities()
    {
        return $this->identities;
    }
}
