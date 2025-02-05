<?php

namespace FluentBoardsPro\App\Modules\CloudStorage\S3;

/**
 * S3 Request class
 *
 * @link http://undesigned.org.za/2007/10/22/amazon-s3-php-class
 * @version 0.5.0-dev
 */
final class S3Request
{
    /**
     * AWS URI
     *
     * @var string
     * @access private
     */
    private $endpoint;

    /**
     * Verb
     *
     * @var string
     * @access private
     */
    private $verb;

    /**
     * S3 bucket name
     *
     * @var string
     * @access private
     */
    private $bucket;

    /**
     * Object URI
     *
     * @var string
     * @access private
     */
    private $uri;

    /**
     * Final object URI
     *
     * @var string
     * @access private
     */
    private $resource = '';

    /**
     * Additional request parameters
     *
     * @var array
     * @access private
     */
    private $parameters = array();

    /**
     * Amazon specific request headers
     *
     * @var array
     * @access private
     */
    private $amzHeaders = array();

    /**
     * HTTP request headers
     *
     * @var array
     * @access private
     */
    private $headers = array(
        'Host' => '', 'Date' => '', 'Content-MD5' => '', 'Content-Type' => ''
    );

    /**
     * Use HTTP PUT?
     *
     * @var bool
     * @access public
     */
    public $fp = false;

    /**
     * PUT file size
     *
     * @var int
     * @access public
     */
    public $size = 0;

    /**
     * PUT post fields
     *
     * @var array
     * @access public
     */
    public $data = false;

    /**
     * S3 request respone
     *
     * @var object
     * @access public
     */
    public $response;


    /**
     * Constructor
     *
     * @param string $verb Verb
     * @param string $bucket Bucket name
     * @param string $uri Object URI
     * @param string $endpoint AWS endpoint URI
     * @return mixed
     */
    function __construct($verb, $bucket = '', $uri = '', $endpoint = 's3.amazonaws.com')
    {
        $this->endpoint = $endpoint;
        $this->verb = $verb;
        $this->bucket = $bucket;
        $this->uri = $uri !== '' ? '/' . str_replace('%2F', '/', rawurlencode($uri)) : '/';

        if ($this->bucket !== '') {
            if ($this->__dnsBucketName($this->bucket)) {
                $this->headers['Host'] = $this->bucket . '.' . $this->endpoint;
                $this->resource = '/' . $this->bucket . $this->uri;
            } else {
                // Old format, deprecated by AWS - removal scheduled for September 30th, 2020
                $this->headers['Host'] = $this->endpoint;
                $this->uri = $this->uri;
                if ($this->bucket !== '') $this->uri = '/' . $this->bucket . $this->uri;
                $this->bucket = '';
                $this->resource = $this->uri;
            }
        } else {
            $this->headers['Host'] = $this->endpoint;
            $this->resource = $this->uri;
        }


        $this->headers['Date'] = gmdate('D, d M Y H:i:s T');
        $this->response = new \stdClass;
        $this->response->error = false;
        $this->response->body = null;
        $this->response->headers = array();
    }


    /**
     * Set request parameter
     *
     * @param string $key Key
     * @param string $value Value
     * @return void
     */
    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }


    /**
     * Set request header
     *
     * @param string $key Key
     * @param string $value Value
     * @return void
     */
    public function setHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }


    /**
     * Set x-amz-meta-* header
     *
     * @param string $key Key
     * @param string $value Value
     * @return void
     */
    public function setAmzHeader($key, $value)
    {
        $this->amzHeaders[$key] = $value;
    }


    /**
     * Get the S3 response
     *
     * @return object | false
     */
    public function getResponse()
    {
        $query = '';
        if (sizeof($this->parameters) > 0) {
            $query = substr($this->uri, -1) !== '?' ? '?' : '&';
            foreach ($this->parameters as $var => $value)
                if ($value == null || $value == '') $query .= $var . '&';
                else $query .= $var . '=' . rawurlencode($value) . '&';
            $query = substr($query, 0, -1);
            $this->uri .= $query;

            if (array_key_exists('acl', $this->parameters) ||
                array_key_exists('location', $this->parameters) ||
                array_key_exists('torrent', $this->parameters) ||
                array_key_exists('website', $this->parameters) ||
                array_key_exists('logging', $this->parameters))
                $this->resource .= $query;
        }
        $url = (S3::$useSSL ? 'https://' : 'http://') . ($this->headers['Host'] !== '' ? $this->headers['Host'] : $this->endpoint) . $this->uri;

        // Basic setup
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_USERAGENT, 'S3/php');

        if (S3::$useSSL) {
            // Set protocol version
            curl_setopt($curl, CURLOPT_SSLVERSION, S3::$useSSLVersion);

            // SSL Validation can now be optional for those with broken OpenSSL installations
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, S3::$useSSLValidation ? 2 : 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, S3::$useSSLValidation ? 1 : 0);

            if (S3::$sslKey !== null) curl_setopt($curl, CURLOPT_SSLKEY, S3::$sslKey);
            if (S3::$sslCert !== null) curl_setopt($curl, CURLOPT_SSLCERT, S3::$sslCert);
            if (S3::$sslCACert !== null) curl_setopt($curl, CURLOPT_CAINFO, S3::$sslCACert);
        }

        curl_setopt($curl, CURLOPT_URL, $url);

        if (S3::$proxy != null && isset(S3::$proxy['host'])) {
            curl_setopt($curl, CURLOPT_PROXY, S3::$proxy['host']);
            curl_setopt($curl, CURLOPT_PROXYTYPE, S3::$proxy['type']);
            if (isset(S3::$proxy['user'], S3::$proxy['pass']) && S3::$proxy['user'] != null && S3::$proxy['pass'] != null)
                curl_setopt($curl, CURLOPT_PROXYUSERPWD, sprintf('%s:%s', S3::$proxy['user'], S3::$proxy['pass']));
        }

        // Headers
        $httpHeaders = array();
        if (S3::hasAuth()) {
            // Authorization string (CloudFront stringToSign should only contain a date)
            if ($this->headers['Host'] == 'cloudfront.amazonaws.com') {
                # TODO: Update CloudFront authentication
                foreach ($this->amzHeaders as $header => $value)
                    if (strlen($value) > 0) $httpHeaders[] = $header . ': ' . $value;

                foreach ($this->headers as $header => $value)
                    if (strlen($value) > 0) $httpHeaders[] = $header . ': ' . $value;

                $httpHeaders[] = 'Authorization: ' . S3::__getSignature($this->headers['Date']);
            } else {
                $this->amzHeaders['x-amz-date'] = gmdate('Ymd\THis\Z');

                if (!isset($this->amzHeaders['x-amz-content-sha256']))
                    $this->amzHeaders['x-amz-content-sha256'] = hash('sha256', $this->data);

                foreach ($this->amzHeaders as $header => $value)
                    if (strlen($value) > 0) $httpHeaders[] = $header . ': ' . $value;

                foreach ($this->headers as $header => $value)
                    if (strlen($value) > 0) $httpHeaders[] = $header . ': ' . $value;

                $httpHeaders[] = 'Authorization: ' . S3::__getSignatureV4(
                        $this->amzHeaders,
                        $this->headers,
                        $this->verb,
                        $this->uri,
                        $this->parameters
                    );

            }
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($curl, CURLOPT_WRITEFUNCTION, array($this, '__responseWriteCallback'));
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, array($this, '__responseHeaderCallback'));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        // Request types
        switch ($this->verb) {
            case 'GET':
                break;
            case 'PUT':
            case 'POST': // POST only used for CloudFront
                if ($this->fp !== false) {
                    curl_setopt($curl, CURLOPT_PUT, true);
                    curl_setopt($curl, CURLOPT_INFILE, $this->fp);
                    if ($this->size >= 0)
                        curl_setopt($curl, CURLOPT_INFILESIZE, $this->size);
                } elseif ($this->data !== false) {
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $this->data);
                } else
                    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
                break;
            case 'HEAD':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
                curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
            case 'DELETE':
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            default:
                break;
        }

        // set curl progress function callback
        if (S3::$progressFunction) {
            curl_setopt($curl, CURLOPT_NOPROGRESS, false);
            curl_setopt($curl, CURLOPT_PROGRESSFUNCTION, S3::$progressFunction);
        }

        // Execute, grab errors
        if (curl_exec($curl)) {
            $this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        } else {
            $this->response->error = array(
                'code'     => curl_errno($curl),
                'message'  => curl_error($curl),
                'resource' => $this->resource
            );
        }

        @curl_close($curl);

        // Parse body into XML
        if ($this->response->error === false && isset($this->response->headers['type']) &&
            $this->response->headers['type'] == 'application/xml' && isset($this->response->body)) {
            $this->response->body = simplexml_load_string($this->response->body);

            // Grab S3 errors
            if (!in_array($this->response->code, array(200, 204, 206)) &&
                isset($this->response->body->Code, $this->response->body->Message)) {
                $this->response->error = array(
                    'code'    => (string)$this->response->body->Code,
                    'message' => (string)$this->response->body->Message
                );
                if (isset($this->response->body->Resource))
                    $this->response->error['resource'] = (string)$this->response->body->Resource;
                unset($this->response->body);
            }
        }

        // Clean up file resources
        if ($this->fp !== false && is_resource($this->fp)) fclose($this->fp);

        return $this->response;
    }


    /**
     * CURL write callback
     *
     * @param resource &$curl CURL resource
     * @param string &$data Data
     * @return integer
     */
    private function __responseWriteCallback($curl, $data)
    {
        if (in_array($this->response->code, array(200, 206)) && $this->fp !== false)
            return fwrite($this->fp, $data);
        else
            $this->response->body .= $data;
        return strlen($data);
    }


    /**
     * Check DNS conformity
     *
     * @param string $bucket Bucket name
     * @return boolean
     */
    private function __dnsBucketName($bucket)
    {
        if (strlen($bucket) > 63 || preg_match("/[^a-z0-9\.-]/", $bucket) > 0) return false;
        if (S3::$useSSL && strstr($bucket, '.') !== false) return false;
        if (strstr($bucket, '-.') !== false) return false;
        if (strstr($bucket, '..') !== false) return false;
        if (!preg_match("/^[0-9a-z]/", $bucket)) return false;
        if (!preg_match("/[0-9a-z]$/", $bucket)) return false;
        return true;
    }


    /**
     * CURL header callback
     *
     * @param resource $curl CURL resource
     * @param string $data Data
     * @return integer
     */
    private function __responseHeaderCallback($curl, $data)
    {
        if (($strlen = strlen($data)) <= 2) return $strlen;
        if (substr($data, 0, 4) == 'HTTP')
            $this->response->code = (int)substr($data, 9, 3);
        else {
            $data = trim($data);
            if (strpos($data, ': ') === false) return $strlen;
            list($header, $value) = explode(': ', $data, 2);
            $header = strtolower($header);
            if ($header == 'last-modified')
                $this->response->headers['time'] = strtotime($value);
            elseif ($header == 'date')
                $this->response->headers['date'] = strtotime($value);
            elseif ($header == 'content-length')
                $this->response->headers['size'] = (int)$value;
            elseif ($header == 'content-type')
                $this->response->headers['type'] = $value;
            elseif ($header == 'etag')
                $this->response->headers['hash'] = $value[0] == '"' ? substr($value, 1, -1) : $value;
            elseif (preg_match('/^x-amz-meta-.*$/', $header))
                $this->response->headers[$header] = $value;
        }
        return $strlen;
    }

}
