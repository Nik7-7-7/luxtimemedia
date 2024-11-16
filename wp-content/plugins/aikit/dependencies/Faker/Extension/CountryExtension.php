<?php

namespace AIKit\Dependencies\Faker\Extension;

/**
 * @experimental This interface is experimental and does not fall under our BC promise
 */
interface CountryExtension extends Extension
{
    /**
     * @example 'Japan'
     */
    public function country(): string;
}
