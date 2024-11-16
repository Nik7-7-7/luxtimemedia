<?php

namespace FluentBoardsPro\App\Modules\CloudStorage;

class Helper
{
    public static function sortMetaHeadersCmp($a, $b)
    {
        $lenA = strlen($a);
        $lenB = strlen($b);
        $minLen = min($lenA, $lenB);
        $ncmp = strncmp($a, $b, $minLen);
        if ($lenA == $lenB) return $ncmp;
        if (0 == $ncmp) return $lenA < $lenB ? -1 : 1;
        return $ncmp;
    }


    /**
     * Create input info array for putObject()
     *
     * @param string $file Input file
     * @param mixed $md5sum Use MD5 hash (supply a string if you want to use your own)
     * @return array | false
     */
    public static function inputFile($file, $md5sum = true)
    {
        if (!file_exists($file) || !is_file($file) || !is_readable($file)) {
            return false;
        }

        clearstatcache(false, $file);
        $md5Sum = $md5sum !== false ? (is_string($md5sum) ? $md5sum : base64_encode(md5_file($file, true))) : '';

        return array('file'      => $file,
                     'size'      => filesize($file),
                     'md5sum'    => $md5Sum,
                     'sha256sum' => hash_file('sha256', $file)
        );
    }
}
