<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Validator_FileUpload extends Quform_Validator_Abstract
{
    const REQUIRED = 'fileUploadRequired';
    const NUM_REQUIRED = 'fileNumRequired';
    const TOO_MANY = 'fileTooMany';
    const TOO_BIG_FILENAME = 'fileTooBigFilename';
    const TOO_BIG = 'fileTooBig';
    const NOT_ALLOWED_TYPE_FILENAME = 'fileNotAllowedTypeFilename';
    const NOT_ALLOWED_TYPE = 'fileNotAllowedType';
    const NOT_UPLOADED_FILENAME = 'fileNotUploadedFilename';
    const NOT_UPLOADED = 'fileNotUploaded';
    const ONLY_PARTIAL_FILENAME = 'fileOnlyPartialFilename';
    const ONLY_PARTIAL = 'fileOnlyPartial';
    const NO_FILE = 'noFile';
    const MISSING_TEMP_FOLDER = 'fileMissingTempFolder';
    const FAILED_TO_WRITE = 'fileFailedToWrite';
    const STOPPED_BY_EXTENSION = 'fileStoppedByExtension';
    const UNKNOWN_ERROR = 'fileUnknownError';
    const BAD_FORMAT = 'fileBadFormat';

    const UPLOAD_ERR_TYPE = 128;
    const UPLOAD_ERR_FILE_SIZE = 129;
    const UPLOAD_ERR_NOT_UPLOADED = 130;

    /**
     * @param   array  $options
     * @throws  InvalidArgumentException  If the name option is not given in the $options
     */
    public function __construct(array $options = array())
    {
        if ( ! array_key_exists('name', $options) || ! Quform::isNonEmptyString($options['name'])) {
            throw new InvalidArgumentException("The 'name' option is required");
        }

        parent::__construct($options);
    }

    /**
     * Returns true if and only if the uploaded file is free of errors
     *
     * @param   array    $value  The element value (array of files)
     * @return  boolean
     */
    public function isValid($value)
    {
        $this->reset();

        $count = count($value);

        if (isset($_FILES[$this->config('name')]) && isset($_FILES[$this->config('name')]['error'])) {
            $file = $_FILES[$this->config('name')];

            if (is_array($file['error'])) {
                foreach ($file['error'] as $key => $error) {
                    if ($error == UPLOAD_ERR_OK) {
                        // The file uploaded OK
                        if ( ! $this->isUploadedFile($file['tmp_name'][$key])) {
                            // The file is not an uploaded file - possibly an attack
                            $this->setFileUploadError(self::UPLOAD_ERR_NOT_UPLOADED, $file['name'][$key]);
                            return false;
                        }

                        if (apply_filters('quform_disallow_empty_files', true) && $file['size'][$key] <= 0) {
                            // The file is empty
                            $this->setFileUploadError(UPLOAD_ERR_NO_FILE, $file['name'][$key]);
                            return false;
                        }

                        if ($this->config('maximumFileSize') > 0 && $file['size'][$key] > $this->config('maximumFileSize')) {
                            // The file is larger than the size allowed by the settings
                            $this->setFileUploadError(self::UPLOAD_ERR_FILE_SIZE, $file['name'][$key]);
                            return false;
                        }

                        $pathInfo = pathinfo($file['name'][$key]);
                        $extension = array_key_exists('extension', $pathInfo) ? strtolower($pathInfo['extension']) : '';

                        if (count($this->config('allowedExtensions')) && ! in_array($extension, $this->config('allowedExtensions'))) {
                            // The file extension is not allowed
                            $this->setFileUploadError(self::UPLOAD_ERR_TYPE, $file['name'][$key]);
                            return false;
                        }

                        if ( ! $this->config('allowAllFileTypes') && ! $this->isAllowedFileType($file['name'][$key])) {
                            // The file type is not allowed by WP core
                            $this->setFileUploadError(self::UPLOAD_ERR_TYPE, $file['name'][$key]);
                            return false;
                        }

                        $count++;
                    } elseif ($error == UPLOAD_ERR_NO_FILE) {
                        continue;
                    } else {
                        $this->setFileUploadError($error, $file['name'][$key]);
                        return false;
                    }
                } // End foreach file
            } else {
                $this->error(self::BAD_FORMAT);
                return false;
            }
        }

        // Check if we have at least one upload if this field is required
        if ($this->config('required') && $count == 0) {
            $this->error(self::REQUIRED);
            return false;
        }

        // Check if they have uploaded the required number of files
        if ($this->config('minimumNumberOfFiles') > 0 && $count < $this->config('minimumNumberOfFiles')) {
            $this->error(self::NUM_REQUIRED, array(
                'min' => $this->config('minimumNumberOfFiles')
            ));
            return false;
        }

        // Check that they haven't uploaded too many files
        if ($this->config('maximumNumberOfFiles') > 0 && $count > $this->config('maximumNumberOfFiles')) {
            $this->error(self::TOO_MANY, array(
                'max' => $this->config('maximumNumberOfFiles')
            ));
            return false;
        }

        return true;
    }

    /**
     * Set the error message corresponding to the error code generated by PHP file uploads and this validator
     *
     * @param   int     $errorCode  The error code
     * @param   string  $filename   The filename to add to the message
     * @return  string              The error message
     */
    protected function setFileUploadError($errorCode, $filename = '')
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
            case self::UPLOAD_ERR_FILE_SIZE:
                if (Quform::isNonEmptyString($filename)) {
                    $this->error(self::TOO_BIG_FILENAME, compact('filename'));
                } else {
                    $this->error(self::TOO_BIG);
                }
                break;
            case UPLOAD_ERR_PARTIAL:
                if (Quform::isNonEmptyString($filename)) {
                    $this->error(self::ONLY_PARTIAL_FILENAME, compact('filename'));
                } else {
                    $this->error(self::ONLY_PARTIAL);
                }
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->error(self::NO_FILE);
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $this->error(self::MISSING_TEMP_FOLDER);
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $this->error(self::FAILED_TO_WRITE);
                break;
            case UPLOAD_ERR_EXTENSION:
                $this->error(self::STOPPED_BY_EXTENSION);
                break;
            case self::UPLOAD_ERR_TYPE:
                if (Quform::isNonEmptyString($filename)) {
                    $this->error(self::NOT_ALLOWED_TYPE_FILENAME, compact('filename'));
                } else {
                    $this->error(self::NOT_ALLOWED_TYPE);
                }
                break;
            case self::UPLOAD_ERR_NOT_UPLOADED:
                if (Quform::isNonEmptyString($filename)) {
                    $this->error(self::NOT_UPLOADED_FILENAME, compact('filename'));
                } else {
                    $this->error(self::NOT_UPLOADED);
                }
                break;
            default:
                $this->error(self::UNKNOWN_ERROR);
                break;
        }
    }

    /**
     * Has the file been uploaded via PHP or the enhanced uploader?
     *
     * @param   string   $filename  The path to the file
     * @return  boolean
     */
    protected function isUploadedFile($filename)
    {
        $isUploadedFile = false;

        if (is_uploaded_file($filename)) {
            $isUploadedFile = true;
        } elseif (preg_match('#[/|\\\]quform[/|\\\]uploads[/|\\\]quf#', $filename)) {
            $isUploadedFile = true;
        }

        return apply_filters('quform_is_uploaded_file', $isUploadedFile, $filename);
    }

    /**
     * Determines if the file type is allowed by WP core
     *
     * @param   string  $filename
     * @return  bool
     */
    protected function isAllowedFileType($filename)
    {
        $file = wp_check_filetype($filename);

        if ( ! $file['ext'] || ! $file['type']) {
            return false;
        }

        return true;
    }

    /**
     * Get all message templates or the single message with the given key
     *
     * @param   string|null   $key
     * @return  array|string
     */
    public static function getMessageTemplates($key = null)
    {
        $messageTemplates = array(
            self::REQUIRED => __('This field is required', 'quform'),
            /* translators: %s: the minimum number of files */
            self::NUM_REQUIRED => sprintf(__('Please upload at least %s file(s)', 'quform'), '%min%'),
            /* translators: %s: the maximum number of files */
            self::TOO_MANY => sprintf(__('You cannot upload more than %s file(s)', 'quform'), '%max%'),
            /* translators: %s: the file name */
            self::TOO_BIG_FILENAME => sprintf(__("File '%s' exceeds the maximum allowed file size", 'quform'), '%filename%'),
            self::TOO_BIG => __('File exceeds the maximum allowed file size', 'quform'),
            /* translators: %s: the file name */
            self::NOT_ALLOWED_TYPE_FILENAME => sprintf(__("File type of '%s' is not allowed", 'quform'), '%filename%'),
            self::NOT_ALLOWED_TYPE => __('File type is not allowed', 'quform'),
            /* translators: %s: the file name */
            self::NOT_UPLOADED_FILENAME => sprintf(__("File '%s' is not an uploaded file", 'quform'), '%filename%'),
            self::NOT_UPLOADED => __('File is not an uploaded file', 'quform'),
            /* translators: %s: the file name */
            self::ONLY_PARTIAL_FILENAME => sprintf(__("File '%s' was only partially uploaded", 'quform'), '%filename%'),
            self::ONLY_PARTIAL => __('File was only partially uploaded', 'quform'),
            self::NO_FILE => __('No file was uploaded', 'quform'),
            self::MISSING_TEMP_FOLDER => __('Missing a temporary folder', 'quform'),
            self::FAILED_TO_WRITE => __('Failed to write file to disk', 'quform'),
            self::STOPPED_BY_EXTENSION => __('File upload stopped by extension', 'quform'),
            self::UNKNOWN_ERROR => __('Unknown upload error', 'quform'),
            self::BAD_FORMAT => __('Data received by the server was not in the expected format', 'quform')
        );

        if (is_string($key)) {
            return array_key_exists($key, $messageTemplates) ? $messageTemplates[$key] : null;
        }

        return $messageTemplates;
    }

    /**
     * Get the default config for this validator
     *
     * @param   string|null  $key  Get the config by key, if omitted the full config is returned
     * @return  array
     */
    public static function getDefaultConfig($key = null)
    {
        $config = apply_filters('quform_default_config_validator_file_upload', array(
            'name' => '',
            'allowedExtensions' => array(),
            'maximumFileSize' => 10485760,
            'minimumNumberOfFiles' => 0,
            'maximumNumberOfFiles' => 1,
            'allowAllFileTypes' => false,
            'required' => false,
            'messages' => array(
                self::REQUIRED => '',
                self::NUM_REQUIRED => '',
                self::TOO_MANY => '',
                self::TOO_BIG_FILENAME => '',
                self::TOO_BIG => '',
                self::NOT_ALLOWED_TYPE_FILENAME => '',
                self::NOT_ALLOWED_TYPE => '',
                self::NOT_UPLOADED_FILENAME => '',
                self::NOT_UPLOADED => '',
                self::ONLY_PARTIAL_FILENAME => '',
                self::ONLY_PARTIAL => '',
                self::NO_FILE => '',
                self::MISSING_TEMP_FOLDER => '',
                self::FAILED_TO_WRITE => '',
                self::STOPPED_BY_EXTENSION => '',
                self::UNKNOWN_ERROR => '',
                self::BAD_FORMAT => ''
            )
        ));

        $config['type'] = 'fileUpload';

        if (Quform::isNonEmptyString($key)) {
            return Quform::get($config, $key);
        }

        return $config;
    }
}
