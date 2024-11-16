<?php
/**
 * Keyword
 *
 * @since     Mar 2023
 * @author    Haydar KULEKCI <haydarkulekci@gmail.com>
 */

namespace AIKit\Dependencies\Qdrant\Models\Field;

class TextField implements FieldSchema
{
    public function schema(): array
    {
        return [
            'type' => 'text',
            'tokenizer' => '',
            'min_token_len' => '',
            'max_token_len' => '',
            'lowercase' => '',
        ];
    }
}