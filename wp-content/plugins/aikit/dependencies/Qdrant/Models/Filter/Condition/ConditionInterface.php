<?php
/**
 * @since     Apr 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace AIKit\Dependencies\Qdrant\Models\Filter\Condition;

interface ConditionInterface
{
    public function toArray(): array;
}