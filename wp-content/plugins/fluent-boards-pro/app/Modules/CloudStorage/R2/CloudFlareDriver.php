<?php

namespace FluentBoardsPro\App\Modules\CloudStorage\R2;


use FluentBoardsPro\App\Modules\CloudStorage\Helper;
use FluentBoardsPro\App\Modules\CloudStorage\RemoteDriver;
use FluentBoardsPro\App\Modules\CloudStorage\S3\S3;

class CloudFlareDriver extends RemoteDriver
{

    private $publicUrl = '';

    private $subFolder = '';

    public function __construct($accessKey, $secretKey, $endpoint, $bucket = '', $region = 'us-east-1')
    {
        parent::__construct($accessKey, $secretKey, $endpoint, $bucket, $region);
    }

    public function setSubFolder($subFolder)
    {
        $this->subFolder = $subFolder;
        return $this;
    }

    public function setPublicUrl($url)
    {
        $this->publicUrl = $url;
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
        if (!$this->publicUrl) {
            return new \WP_Error('public_url_not_set', 'Public URL not set', []);
        }

        return $this->publicUrl . '/' . $objectName;
    }

    public function getRemotePath($objectName)
    {
        return 'r2://' . $this->endpoint . '/' . $this->bucket . '/' . $objectName;
    }

    public function deleteObject($path)
    {
        // check if it stats with r2://
        if (strpos($path, 'r2://') !== 0) {
            return new \WP_Error('invalid_path', 'Invalid path', []);
        }

        $objectName = str_replace('r2://' . $this->endpoint . '/' . $this->bucket . '/', '', $path);

        $driver = $this->getDriver();
        return $driver::deleteObject($this->bucket, $objectName);
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
