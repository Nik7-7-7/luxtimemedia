<?php

namespace FluentBoardsPro\App\Modules\CloudStorage;

use FluentBoardsPro\App\Services\ProHelper;

class StorageHelper
{
    public static function getConfig($mode = 'internal')
    {
        if (defined('FLUENT_BOARDS_CLOUD_STORAGE') && FLUENT_BOARDS_CLOUD_STORAGE) {
            $config = [
                'is_defined' => true,
                'driver'     => FLUENT_BOARDS_CLOUD_STORAGE,
                'account_id' => defined('FLUENT_BOARDS_CLOUD_STORAGE_ACCOUNT_ID') ? FLUENT_BOARDS_CLOUD_STORAGE_ACCOUNT_ID : '',
                'region'     => defined('FLUENT_BOARDS_CLOUD_STORAGE_REGION') ? FLUENT_BOARDS_CLOUD_STORAGE_REGION : '',
                'access_key' => defined('FLUENT_BOARDS_CLOUD_STORAGE_ACCESS_KEY') ? FLUENT_BOARDS_CLOUD_STORAGE_ACCESS_KEY : '',
                'secret_key' => defined('FLUENT_BOARDS_CLOUD_STORAGE_SECRET_KEY') ? FLUENT_BOARDS_CLOUD_STORAGE_SECRET_KEY : '',
                'sub_folder' => defined('FLUENT_BOARDS_CLOUD_STORAGE_SUB_FOLDER') ? FLUENT_BOARDS_CLOUD_STORAGE_SUB_FOLDER : '',
                'bucket'     => defined('FLUENT_BOARDS_CLOUD_STORAGE_BUCKET') ? FLUENT_BOARDS_CLOUD_STORAGE_BUCKET : '',
                'public_url' => defined('FLUENT_BOARDS_CLOUD_STORAGE_PUBLIC_URL') ? FLUENT_BOARDS_CLOUD_STORAGE_PUBLIC_URL : ''
            ];

            if ($mode === 'internal') {
                return $config;
            }

            return self::maybeAddDummyKeys($config);
        }

        $config = fluent_boards_get_option('_storage_config', []);

        $defaults = [
            'driver' => 'local'
        ];

        $config = wp_parse_args($config, $defaults);

        if ($mode === 'internal') {
            return self::maybeDecryptKeys($config);
        }

        return self::maybeAddDummyKeys($config);

    }

    public static function updateConfig($config)
    {
        if (defined('FLUENT_BOARDS_CLOUD_STORAGE') && FLUENT_BOARDS_CLOUD_STORAGE) {
            return false;
        }

        $config = self::maybeEncryptKeys($config);
        fluent_boards_update_option('_storage_config', $config);
        return true;
    }


    private static function maybeEncryptKeys($config)
    {
        if ($config['driver'] == 'local') {
            return $config;
        }

        if (!empty($config['access_key'])) {
            $config['access_key'] = ProHelper::encryptDecrypt($config['access_key'], 'e');
        }

        if (!empty($config['secret_key'])) {
            $config['secret_key'] = ProHelper::encryptDecrypt($config['secret_key'], 'e');
        }

        return $config;
    }

    private static function maybeDecryptKeys($config)
    {
        if ($config['driver'] === 'local') {
            return $config;
        }

        if (!empty($config['access_key'])) {
            $config['access_key'] = ProHelper::encryptDecrypt($config['access_key'], 'd');
        }

        if (!empty($config['secret_key'])) {
            $config['secret_key'] = ProHelper::encryptDecrypt($config['secret_key'], 'd');
        }

        return $config;
    }

    private static function maybeAddDummyKeys($config)
    {
        if ($config['driver'] === 'local') {
            return $config;
        }

        if (!empty($config['access_key'])) {
            $config['access_key'] = 'FBS_ENCRYPTED_DATA_KEY';
        }

        if (!empty($config['secret_key'])) {
            $config['secret_key'] = 'FBS_ENCRYPTED_DATA_KEY';
        }

        return $config;
    }
}
