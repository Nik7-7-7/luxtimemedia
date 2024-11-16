<?php

namespace FluentBoardsPro\App\Modules\CloudStorage\Google;

use FluentBoardsPro\App\Modules\CloudStorage\Helper;
use FluentBoardsPro\App\Modules\CloudStorage\RemoteDriver;
use FluentBoardsPro\App\Modules\CloudStorage\S3\S3;

class GCSDriver extends RemoteDriver
{
    private $subFolder = '';
    public function __construct($accessKey, $secretKey, $endpoint, $bucket = '', $region = 'auto')
    {
        parent::__construct($accessKey, $secretKey, $endpoint, $bucket, $region);
    }

    public function setSubFolder($subFolder)
    {
        $this->subFolder = $subFolder;
        return $this;
    }

    public function putObject($mediaPath)
    {
        $inputFile = Helper::inputFile($mediaPath);
        if (!$inputFile) {
            return new \WP_Error('file_not_found', 'File not found', []);
        }

        $s3Driver = $this->getDriver();

        $objectName = basename($mediaPath);

        if ($this->subFolder) {
            $objectName = $this->subFolder . '/' . $objectName;
        }

        $response = $s3Driver::putObject($inputFile, $this->bucket, $objectName, S3::ACL_PUBLIC_READ);

        if (!$response || $response->code !== 200) {
            return new \WP_Error('s3_error', 'Error uploading file to S3', $response->error);
        }

        $publicUrl = $this->getPublicUrl($objectName);

        if (is_wp_error($publicUrl)) {
            return $publicUrl;
        }

        return [
            'public_url'  => $publicUrl,
            'remote_path' => $this->getRemotePath($objectName)
        ];
    }

    public function getPublicUrl($objectName)
    {
        return 'https://storage.googleapis.com/' . $this->bucket . '/' . $objectName;
    }

    public function getRemotePath($objectName)
    {
        return 'gs://' . $this->bucket . '/' . $objectName;
    }

    public function deleteObject($path)
    {
        try {
            // check if it starts with gs://
            if (strpos($path, 'gs://') !== 0) {
                return new \WP_Error('invalid_path', 'Invalid path', []);
            }

            // Extract the object name from the path
            $objectName = str_replace('gs://' . $this->bucket . '/', '', $path);
        } catch (\Exception $e) {
            return new \WP_Error('gcs_error', 'Error deleting file from GCS: ' . $e->getMessage(), []);
        }
    }

    public function testConnection()
    {
        // get files from the bucket
        $s3Driver = $this->getDriver();

        try {
            $s3Driver::getBucket($this->bucket, null, null, 1);
        } catch (\Exception $exception) {
            return new \WP_Error('s3_error', $exception->getMessage(), []);
        }

        return true;
    }
}