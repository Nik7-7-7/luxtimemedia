<?php
/**
 * Keyword
 *
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace AIKit\Dependencies\Qdrant\Models\Field;

class KeywordField implements FieldSchema
{
    public function schema(): string
    {
        return 'keyword';
    }
}