<?php

function aikit_cut_long_string_into_smaller_chunks($string, $statements_per_paragraph = 5) {
    // Split the string into an array of lines
    $lines = preg_split('/(\n|\.)\s*/', $string, -1, PREG_SPLIT_DELIM_CAPTURE);

    $chunks = array();
    $chunk = '';
    $statement_count = 0;

    // Loop through each line and add it to the current chunk
    foreach ($lines as $line) {
        // If the line is empty, skip it
        if (trim($line) === '') {
            continue;
        }

        // Add the line to the current chunk
        $chunk .= '' . $line;

        // Count the number of statements in the line
        $statement_count += substr_count($line, '.') + substr_count($line, "\n");

        // If the chunk now has at least 5 statements, add it to the list of chunks
        if ($statement_count >= $statements_per_paragraph) {
            $chunks[] = $chunk;
            $chunk = '';
            $statement_count = 0;
        }
    }

    // If there is a partial chunk remaining, add it to the list of chunks
    if ($chunk !== '') {
        $chunks[] = $chunk;
    }

    return $chunks;
}
