<?php

namespace FluentBoardsPro\App\Modules\CloudStorage;

use FluentBoardsPro\App\Modules\CloudStorage\S3\S3;

abstract class RemoteDriver
{
    protected $bucket;
    protected $accessKey;
    protected $secretKey;
    protected $endpoint;
    protected $region;

    public function __construct($accessKey, $secretKey, $endpoint, $bucket, $region = 'us-east-1')
    {
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->endpoint = $endpoint;
        $this->bucket = $bucket;
        $this->region = $region;
    }

    public function getDriver()
    {
        return new S3($this->accessKey, $this->secretKey, true, $this->endpoint, $this->region);
    }

    abstract public function putObject($mediaPath);

    abstract public function getPublicUrl($objectName);

    abstract public function getRemotePath($objectName);

    abstract public function deleteObject($path);

    abstract public function testConnection();
}
