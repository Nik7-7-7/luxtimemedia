<?php

/**
 * @copyright Copyright (c) 2009-2022 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Uploader
{
    /**
     * @var array
     */
    protected static $fileKeys = array('error', 'name', 'size', 'tmp_name', 'type');

    /**
     * @var Quform_Session
     */
    protected $session;

    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Form_Factory
     */
    protected $formFactory;

    /**
     * @param  Quform_Session       $session
     * @param  Quform_Repository    $repository
     * @param  Quform_Form_Factory  $formFactory
     */
    public function __construct(Quform_Session $session, Quform_Repository $repository,
                                Quform_Form_Factory $formFactory)
    {
        $this->session = $session;
        $this->repository = $repository;
        $this->formFactory = $formFactory;
    }

    /**
     * Hook entry point for handling uploads via Ajax
     */
    public function upload()
    {
        if ( ! Quform::isPostRequest() || Quform::get($_POST, 'quform_ajax_uploading') != '1') {
            return;
        }

        $this->validateUploadRequest();
        $this->handleUploadRequest();
    }

    /**
     * Handle the request to upload a file via Ajax
     */
    protected function handleUploadRequest()
    {
        $config = $this->repository->getConfig((int) $_POST['quform_form_id']);

        if ( ! is_array($config)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Could not find the form config', 'quform')
            ));
        }

        $config['uniqueId'] = $_POST['quform_form_uid'];

        $form = $this->formFactory->create($config);

        if ( ! ($form instanceof Quform_Form) || $form->config('trashed')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Could not find the form', 'quform')
            ));
        }

        if ( ! $form->isActive()) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('This form is not currently active', 'quform')
            ));
        }

        $element = $form->getElementById((int) $_POST['quform_element_id']);

        if ( ! ($element instanceof Quform_Element_File)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Could not find the element', 'quform')
            ));
        }

        if ( ! isset($_FILES[$element->getName()])) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('File data not found', 'quform')
            ));
        }

        $uploadsTmpDir = $this->getUploadsTempDir();

        if ( ! is_dir($uploadsTmpDir)) {
            wp_mkdir_p($uploadsTmpDir);
        }

        if ( ! wp_is_writable($uploadsTmpDir)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Temporary uploads directory is not writable', 'quform')
            ));
        }

        // Disable the minimum number of files validator check while handling this upload
        $validator = $element->getFileUploadValidator();
        $validator->setConfig('minimumNumberOfFiles', 0);

        if ($element->isValid()) {
            $sessionKey = $form->getSessionKey() . '.uploads.' . $element->getName();

            // Generate a unique ID for this upload
            $uniqueId = $this->generateUploadUid();

            // Save the upload data into session
            $filename = tempnam($uploadsTmpDir, 'quform');
            move_uploaded_file($_FILES[$element->getName()]['tmp_name'][0], $filename);
            $_FILES[$element->getName()]['tmp_name'][0] = $filename;

            $files = $this->session->has($sessionKey) ? $this->session->get($sessionKey) : array();

            foreach (self::$fileKeys as $key) {
                $files[$key][] = $_FILES[$element->getName()][$key][0];
            }

            $files['quform_upload_uid'][] = $uniqueId;
            $files['timestamp'][] = time();

            $this->session->set($sessionKey, $files);

            wp_send_json(array(
                'type' => 'success',
                'uid' => $uniqueId
            ));
        } else {
            wp_send_json(array(
                'type' => 'error',
                'message' => $element->getError()
            ));
        }
    }

    /**
     * Get the path to the temporary uploads directory
     *
     * @return string
     */
    protected function getUploadsTempDir()
    {
        return Quform::getTempDir('/quform/uploads');
    }

    /**
     * Validate the request to upload a file via Ajax
     */
    protected function validateUploadRequest()
    {
        if ( ! isset($_POST['quform_form_id'], $_POST['quform_form_uid'], $_POST['quform_element_id']) ||
             ! is_numeric($_POST['quform_form_id']) ||
             ! Quform_Form::isValidUniqueId($_POST['quform_form_uid']) ||
             ! is_numeric($_POST['quform_element_id'])
        ) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }
    }

    /**
     * Handle upload processing
     *
     * @param Quform_Form $form
     */
    public function process(Quform_Form $form)
    {
        foreach ($form->getRecursiveIterator() as $element) {
            if ( ! ($element instanceof Quform_Element_File)) {
                continue;
            }

            $elementName = $element->getName();

            if ( ! array_key_exists($elementName, $_FILES) || ! is_array($_FILES[$elementName])) {
                continue;
            }

            $files = $_FILES[$elementName];

            if (is_array($files['error'])) {
                foreach ($files['error'] as $key => $error) {
                    if ($error == UPLOAD_ERR_OK) {
                        // Normalise the array structure
                        $file = array();

                        foreach (self::$fileKeys as $k) {
                            $file[$k] = $files[$k][$key];
                        }

                        // Save the upload unique ID, generate one if it doesn't exist (e.g. from non-Ajax uploads)
                        $file['quform_upload_uid'] = isset($files['quform_upload_uid'][$key]) && $this->isValidUploadUid($files['quform_upload_uid'][$key]) ? $files['quform_upload_uid'][$key] : $this->generateUploadUid();
                        $file['timestamp'] = isset($files['timestamp'][$key]) ? $files['timestamp'][$key] : time();

                        $this->processUploadedFile($file, $element, $form);
                    }
                }
            }
        }
    }

    /**
     * Process the uploaded file
     *
     * @param   array                $file     The file data
     * @param   Quform_Element_File  $element  The Quform file element instance
     * @param   Quform_Form          $form     The form instance
     */
    protected function processUploadedFile(array $file, Quform_Element_File $element, Quform_Form $form)
    {
        $pathInfo = pathinfo($file['name']);
        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';

        $filename = Quform::isNonEmptyString($extension) ? str_replace(".$extension", '', $pathInfo['basename']) : $pathInfo['basename'];
        $filename = sanitize_file_name($filename);
        $filename = apply_filters('quform_filename_' . $element->getName(), $filename, $element, $form); /* Deprecated */
        $filename = apply_filters('quform_upload_filename_' . $element->getIdentifier(), $filename, $file, $element, $form);

        if (Quform::isNonEmptyString($extension)) {
            $filename = Quform::isNonEmptyString($filename) ? "$filename.$extension" : "upload.$extension";
        } else {
            $filename = Quform::isNonEmptyString($filename) ? $filename : 'upload';
        }

        $file['name'] = $filename;
        $file['path'] = $file['tmp_name'];

        unset($file['error'], $file['tmp_name']);

        if ($element->config('saveToServer')) {
            $result = $this->saveUploadedFile($file, $element, $form);

            if (is_array($result)) {
                $file = $result;

                if ($element->config('addToMediaLibrary')) {
                    $this->addToMediaLibrary($file, $element, $form);
                }
            }
        } else {
            // Rename the file to the actual filename so that attachments work correctly
            $tmpPath = trailingslashit(dirname($file['path']));

            // Check if the file name already exists, if so generate a new one
            if (file_exists($tmpPath . $file['name'])) {
                $count = 1;
                $newFilenamePath = $tmpPath . $file['name'];

                while (file_exists($newFilenamePath)) {
                    $newFilename = $count++ . '_' . $file['name'];
                    $newFilenamePath = $tmpPath . $newFilename;
                }

                $file['name'] = $newFilename;
            }

            // Move the file
            if (rename($file['path'], $tmpPath . $file['name']) !== false) {
                chmod($tmpPath . $file['name'], 0644);

                $file['path'] = $tmpPath . $file['name'];
            }
        }

        $element->addFile($file);
    }

    /**
     * Add the given file to the WordPress media library
     *
     * @param array                $file     The file data
     * @param Quform_Element_File  $element  The File element instance
     * @param Quform_Form          $form     The form instance
     */
    protected function addToMediaLibrary(array $file, Quform_Element_File $element, Quform_Form $form)
    {
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $type = wp_check_filetype($file['name']);

        $attachment = array(
            'post_title' => $file['name'],
            'post_content' => '',
            'post_mime_type' => $type['type'],
            'guid' => $file['url']
        );

        $attachment = apply_filters('quform_uploader_attachment', $attachment, $file, $element, $form);
        $attachment = apply_filters('quform_uploader_attachment_' . $element->getIdentifier(), $attachment, $file, $element, $form);

        $attachId = wp_insert_attachment($attachment, $file['path']);
        wp_update_attachment_metadata($attachId, wp_generate_attachment_metadata($attachId, $file['path']));
    }

    /**
     * Save the uploaded file
     *
     * TODO support files outside of the WP uploads DIR
     *
     * @param   array                 $file     The file data
     * @param   Quform_Element_File   $element  The Quform file element instance
     * @param   Quform_Form           $form     The form instance
     * @return  array|bool                      The file data or false on failure
     */
    protected function saveUploadedFile(array $file, Quform_Element_File $element, Quform_Form $form)
    {
        if (($wpUploadsDir = Quform::getUploadsDir()) == false) {
            // Uploads dir is not writable
            return false;
        }

        // Get the save path
        $path = $element->config('savePath') == '' ? 'quform/{form_id}-{upload_security_token}/{year}/{month}/' : $element->config('savePath');

        // Replace placeholders
        $path = str_replace(
            array(
                '{form_id}',
                '{year}',
                '{month}',
                '{day}',
                '{upload_security_token}'
            ),
            array(
                $form->getId(),
                Quform::date('Y'),
                Quform::date('m'),
                Quform::date('d'),
                md5($form->getId() . $form->config('createdAt'))
            ),
            $path
        );

        // Apply any filter hooks to the path
        $path = apply_filters('quform_upload_path', $path, $element, $form);
        $path = apply_filters('quform_upload_path_' . $form->getId(), $path, $element, $form);

        // Join the path with the WP uploads directory
        $absolutePath = rtrim($wpUploadsDir, '/') . '/' . ltrim($path, '/');

        // Apply filters to the absolute path
        $absolutePath = apply_filters('quform_upload_absolute_path', $absolutePath, $element, $form);
        $absolutePath = apply_filters('quform_upload_absolute_path_' . $form->getId(), $absolutePath, $element, $form);

        // Add a trailing slash
        $path = trailingslashit($path);
        $absolutePath = trailingslashit($absolutePath);

        // Make the upload directory if it's not set
        if (!is_dir($absolutePath)) {
            wp_mkdir_p($absolutePath);
        }

        // Check if the file name already exists, if so generate a new one
        if (file_exists($absolutePath . $file['name'])) {
            $count = 1;
            $newFilenamePath = $absolutePath . $file['name'];

            while (file_exists($newFilenamePath)) {
                $newFilename = $count++ . '_' . $file['name'];
                $newFilenamePath = $absolutePath . $newFilename;
            }

            $file['name'] = $newFilename;
        }

        // Move the file
        if (rename($file['path'], $absolutePath . $file['name']) !== false) {
            chmod($absolutePath . $file['name'], 0644);

            $file['path'] = $absolutePath . $file['name'];
            $file['url'] = Quform::getUploadsUrl($path . $file['name']);

            return $file;
        } else {
            return false;
        }
    }

    /**
     * Merge files uploaded by the enhanced uploader into the $_FILES array and set file upload element values from session if set
     *
     * @param Quform_Form $form
     */
    public function mergeSessionFiles(Quform_Form $form)
    {
        $uploads = $this->session->get(sprintf('%s.uploads', $form->getSessionKey()));

        // Files the user removed
        $removedUploadUids = isset($_POST['quform_removed_upload_uids']) && Quform::isNonEmptyString($_POST['quform_removed_upload_uids']) ? explode(',', $_POST['quform_removed_upload_uids']) : array();

        if (is_array($uploads)) {
            foreach ($uploads as $elementName => $uploadInfo) {
                if (is_array($uploadInfo['quform_upload_uid'])) {
                    // Multiple file upload
                    foreach ($uploadInfo['quform_upload_uid'] as $key => $id) {
                        if (in_array($id, $removedUploadUids)) {
                            foreach (self::$fileKeys as $fileKey) {
                                unset($uploads[$elementName][$fileKey][$key]);
                            }
                            unset($uploads[$elementName]['quform_upload_uid'][$key]);
                        }
                    }

                    // If there are no uploads remaining just remove the whole array
                    if ( ! count($uploads[$elementName]['quform_upload_uid'])) {
                        unset($uploads[$elementName]);
                    }
                } else {
                    // Single file upload
                    if (in_array($uploadInfo['quform_upload_uid'], $removedUploadUids)) {
                        unset($uploads[$elementName]);
                    }
                }
            }

            // Merge them into $_FILES
            $_FILES = array_merge($_FILES, $uploads);
        }

        $files = $this->session->get(sprintf('%s.files', $form->getSessionKey()));

        if (is_array($files)) {
            foreach ($files as $elementName => $value) {
                $element = $form->getElementByName($elementName);

                foreach ($value as $key => $file) {
                    if (in_array($file['quform_upload_uid'], $removedUploadUids)) {
                        unset($value[$key]);
                    }
                }

                $value = array_values($value); // reindex the array

                if ($element instanceof Quform_Element_File) {
                    $element->setValue($value);
                }
            }
        }
    }

    /**
     * Save the values of file upload fields into session
     *
     * @param Quform_Form $form
     */
    public function saveFileUploadValuesIntoSession(Quform_Form $form)
    {
        foreach ($form->getRecursiveIterator() as $element) {
            if ( ! $element instanceof Quform_Element_File) {
                continue;
            }

            if ( ! $element->isEmpty()) {
                $this->session->set($form->getSessionKey() . '.files.' . $element->getName(), $element->getValue());
            }
        }
    }

    /**
     * Save file upload data from the $_FILES array into session
     *
     * @param Quform_Form $form
     */
    public function saveUploadedFilesIntoSession(Quform_Form $form)
    {
        foreach ($form->getRecursiveIterator() as $element) {
            if ( ! $element instanceof Quform_Element_File) {
                continue;
            }

            $elementName = $element->getName();

            if ( ! array_key_exists($elementName, $_FILES) || ! is_array($_FILES[$elementName])) {
                continue;
            }

            $uploadsTmpDir = $this->getUploadsTempDir();

            if ( ! is_dir($uploadsTmpDir)) {
                wp_mkdir_p($uploadsTmpDir);
            }

            if ( ! wp_is_writable($uploadsTmpDir)) {
                continue;
            }

            if ($element->isValid()) {
                $sessionKey = $form->getSessionKey() . '.uploads.' . $elementName;
                $files = array();

                foreach ($_FILES[$elementName]['error'] as $key => $error) {
                    if ($error == UPLOAD_ERR_OK) {
                        if (is_uploaded_file($_FILES[$elementName]['tmp_name'][$key])) {
                            $filename = tempnam($uploadsTmpDir, 'quform');
                            move_uploaded_file($_FILES[$elementName]['tmp_name'][$key], $filename);
                            $_FILES[$elementName]['tmp_name'][$key] = $filename;
                        }

                        foreach (self::$fileKeys as $fileKey) {
                            $files[$fileKey][] = $_FILES[$elementName][$fileKey][$key];
                            $file[$fileKey] = $_FILES[$elementName][$fileKey][$key];
                        }

                        $files['quform_upload_uid'][] = isset($_FILES[$elementName]['quform_upload_uid'][$key]) && $this->isValidUploadUid($_FILES[$elementName]['quform_upload_uid'][$key]) ? $_FILES[$elementName]['quform_upload_uid'][$key] : $this->generateUploadUid();
                        $files['timestamp'][] = time();

                        $element->addFile($file);
                    }
                }

                if (count($files)) {
                    $this->session->set($sessionKey, $files);
                } else {
                    $this->session->forget($sessionKey);
                }
            }
        }
    }

    /**
     * Check that the given upload uid is valid
     *
     * @param   string  $uid
     * @return  bool
     */
    protected function isValidUploadUid($uid)
    {
        return is_string($uid) && preg_match('/^[a-zA-Z0-9]{40}$/', $uid);
    }

    /**
     * Generate an upload unique ID
     *
     * @return string
     */
    protected function generateUploadUid()
    {
        return Quform::randomString(40);
    }

    /**
     * Deletes any files uploaded via the enhanced uploader that were temporarily
     * stored in the system temp directory but were never used.
     */
    public function cleanup()
    {
        $uploadsTmpDir = $this->getUploadsTempDir();

        if (is_dir($uploadsTmpDir) && $handle = opendir($uploadsTmpDir)) {
            clearstatcache();
            $keepUntil = time() - 21600; // Delete anything older than six hours (60 * 60 * 6)
            while (false !== ($file = readdir($handle))) {
                $filePath = $uploadsTmpDir . '/' . $file;
                $mtime = filemtime($filePath);
                if ($file != '.' && $file != '..' && $mtime < $keepUntil) {
                    @unlink($filePath);
                }
            }

            closedir($handle);
        }
    }

    /**
     * Schedule the task to cleanup unused uploads
     */
    protected function scheduleCleanup()
    {
        if ( ! wp_next_scheduled('quform_upload_cleanup')) {
            wp_schedule_event(time() + (12 * HOUR_IN_SECONDS), 'twicedaily', 'quform_upload_cleanup');
        }
    }

    /**
     * Unschedule the task to cleanup unused uploads
     */
    protected function unscheduleCleanup()
    {
        if ($timestamp = wp_next_scheduled('quform_upload_cleanup')) {
            wp_unschedule_event($timestamp, 'quform_upload_cleanup');
        }
    }

    /**
     * Schedule the the task to cleanup unused uploads
     *
     * Called on plugin activation
     */
    public function activate()
    {
        $this->scheduleCleanup();

        $uploadsTmpDir = $this->getUploadsTempDir();

        if ( ! is_dir($uploadsTmpDir)) {
            wp_mkdir_p($uploadsTmpDir);
        }
    }

    /**
     * Unschedule the task to cleanup unused uploads, and run the cleanup
     *
     * Called on plugin deactivation
     */
    public function deactivate()
    {
        $this->unscheduleCleanup();
        $this->cleanup();
    }

    /**
     * Unschedule the task to cleanup unused uploads, and run the cleanup
     *
     * Called on plugin uninstall
     */
    public function uninstall()
    {
        $this->unscheduleCleanup();
        $this->cleanup();
    }
}
