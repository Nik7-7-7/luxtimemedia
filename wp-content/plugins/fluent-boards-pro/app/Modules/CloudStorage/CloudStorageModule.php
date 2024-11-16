<?php

namespace FluentBoardsPro\App\Modules\CloudStorage;

use FluentBoardsPro\App\Modules\CloudStorage\R2\CloudFlareDriver;
use FluentCommunity\Modules\CloudStorage\S3\S3Driver;

class CloudStorageModule
{
    public function register($app)
    {
        add_filter('fluent_boards/upload_media_data', function ($mediaData) {
            $remoteDriver = $this->getDriver();

            if (!$remoteDriver) {
                return $mediaData;
            }

            $mediaPath = $mediaData['file_path'];

            try {
                $response = $remoteDriver->putObject($mediaPath);
            } catch (\Exception $exception) {
                return $mediaData;
            }

            if (!$response || is_wp_error($response)) {
                return $mediaData;
            }

            $mediaData['file_path'] = $response['remote_path'];
            $mediaData['full_url'] = $response['public_url'];
            $mediaData['driver'] = $this->getDriverName($remoteDriver);

            // unlink the old file now
            @unlink($mediaPath);

            return $mediaData;
        });

        add_action('fluent_boards/delete_remote_media_s3', [$this, 'deleteRemoteMedia']);

    }

    public function deleteRemoteMedia($media)
    {
        $remoteDriver = $this->getDriver();
        if (!$remoteDriver) {
            return;
        }

        try {
            $remoteDriver->deleteObject($media->media_path);
        } catch (\Exception $exception) {
            // do nothing for now
        }
    }

    public function getDriver()
    {
        return $this->getConnectionDriver();
    }

    public function getDriverName($driver)
    {
        if ($driver instanceof CloudFlareDriver) {
            return 'cloudflare_r2';
        }

        if ($driver instanceof S3Driver) {
            return 'amazon_s3';
        }

        return 'local';
    }

    public function getConnectionDriver($config = null)
    {
        if (!$config) {
            $config = StorageHelper::getConfig();
        }

        if ($config['driver'] == 'local') {
            return null;
        }

        if ($config['driver'] === 'cloudflare_r2') {
            return $this->cloudflareDriver($config);
        }

        if ($config['driver'] === 'amazon_s3') {
            return $this->s3Driver($config);
        }

        return null;
    }

    private function cloudflareDriver($config = null)
    {
        if (!$config) {
            $config = StorageHelper::getConfig();
        }

        if (
            empty($config['access_key']) ||
            empty($config['secret_key']) ||
            !isset($config['bucket']) ||
            !isset($config['public_url']) ||
            !isset($config['account_id']) ||
            $config['driver'] != 'cloudflare_r2'
        ) {
            return null;
        }

        $endPoint = $config['account_id'] . '.r2.cloudflarestorage.com';

        $driver = (new CloudFlareDriver($config['access_key'], $config['secret_key'], $endPoint, $config['bucket']))
            ->setPublicUrl($config['public_url']);

        if (!empty($config['sub_folder'])) {
            $driver = $driver->setSubFolder($config['sub_folder']);
        }

        return $driver;
    }

    private function s3Driver($config = null)
    {
        if (!$config) {
            $config = StorageHelper::getConfig();
        }

        if (
            empty($config['region']) ||
            empty($config['access_key']) ||
            empty($config['secret_key']) ||
            !isset($config['bucket']) ||
            $config['driver'] != 'amazon_s3'
        ) {
            return null;
        }

        $endPoint = defined('FLUENT_BOARDS_CLOUD_STORAGE_ENDPOINT') ? FLUENT_BOARDS_CLOUD_STORAGE_ENDPOINT : 's3.amazonaws.com';

        $driver = new S3Driver($config['access_key'], $config['secret_key'], $endPoint, $config['bucket'], $config['region']);

        if (!empty($config['sub_folder'])) {
            $driver = $driver->setSubFolder($config['sub_folder']);
        }

        return $driver;
    }
}
