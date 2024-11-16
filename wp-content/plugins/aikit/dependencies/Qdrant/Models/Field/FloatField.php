<?php
/**
 * Float
 *
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace AIKit\Dependencies\Qdrant\Models\Field;

class FloatField implements FieldSchema
{
    public function schema(): string
    {
        return 'float';
    }
}