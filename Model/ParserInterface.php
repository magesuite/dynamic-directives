<?php

namespace MageSuite\DynamicDirectives\Model;

interface ParserInterface
{
    /**
     * @param $text
     * @return Directive[]
     */
    public function getDirectives($text);
}
