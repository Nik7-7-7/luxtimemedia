<?php

namespace FluentBoardsPro\App\Http\Controllers;

use DateTimeImmutable;
use FluentBoards\App\Http\Controllers\Controller;
use FluentBoards\Framework\Http\Request\Request;
use FluentBoards\Framework\Support\Arr;
use FluentBoardsPro\App\Services\Constant;
use FluentBoardsPro\App\Services\FluentBoardsImporter;
use FluentBoardsPro\App\Services\TrelloImporter;
use FluentBoardsPro\App\Services\AsanaImporter;

class ImportController extends Controller
{
    public function importFile(Request $request)
    {
        $file = Arr::get($request->files(), 'file')->toArray();
        $importFrom = $request->getSafe('importFrom');

        if(!$file) {
            throw new \Exception('File is empty.');
        }

        $fileSize = $file['size'];
        // Get the upload directory information
        $uploadDir = wp_upload_dir();
        // Check if the upload directory information is available
        if (empty($uploadDir['basedir'])) {
            throw new \Exception('Upload directory information not available.');
        }

        // Define the user-specific directory for task attachments
        $userDirName = $uploadDir['basedir'] . '/Fluent-Boards/File/';
        $userBaseUrl = $uploadDir['baseurl'] . '/Fluent-Boards/File/';

        // Create the user-specific directory if it doesn't exist
        if (!file_exists($userDirName)) {
            wp_mkdir_p($userDirName);
        }

        $currentTimeStamp = (new DateTimeImmutable())->getTimestamp();
        // Generate a unique filename for the uploaded file
        $fileName = $currentTimeStamp . '-' . wp_unique_filename($userDirName, $file['name']);
        // Move the uploaded file to the user-specific directory
        $fileUploaded = move_uploaded_file($file['tmp_name'], $userDirName . '' . $fileName);

        // Build the file URL based on the upload directory and generated filename
        $fileUrl = $userBaseUrl . '' . $fileName;
        $filePath = $userDirName . '' . $fileName;
        $jsonString = file_get_contents($filePath);

        $importData = json_decode($jsonString, true);

        $board = [];
        if($importFrom == 'Trello'){
            $board = $this->importFromTrello($importData);
        }elseif($importFrom == 'Asana'){
            $board = $this->importFromAsana($importData);
        }elseif($importFrom == 'FluentBoards'){
            $board = $this->importFromFluentBoards($importData);
        }

        if(!$board){
            return $this->sendError([
                'message' => __("Invalid JSON.", 'fluent-boards-pro'),
                'type'    => 'warning',
            ]);
        }

        // Update the upload success flag based on the result of the file move operation
        $uploadSuccess = !empty($fileUploaded);

        return $this->sendSuccess([
            'message'    => __('Processed and Imported successfully', 'fluent-boards-pro'),
            'board_id' => $board ? $board->id: null,
            'success' => $uploadSuccess,
        ], 200);

    }

    private function importFromTrello($importData)
    {
        try {
            if (!isset($importData['url'])) {
                return null;
            } else {
                if (!str_contains($importData['url'], Constant::TRELLO_URL)) {
                    return null;
                } else {
                    return TrelloImporter::process($importData);
                }
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    private function importFromAsana($importData)
    {
        try {
            if (!isset($importData['data'])) {
                return null;
            } else {
                if (!str_contains($importData['data'][0]['permalink_url'], Constant::ASANA_URL)) {
                    return null;
                } else {
                    return AsanaImporter::process($importData);
                }
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
    private function importFromFluentBoards($importData)
    {
        try {
            if (!isset($importData['key'])) {
                return null;
            } else {
                if (!str_contains($importData['key'], Constant::FLUENT_BOARDS_IMPORT)) {
                    return null;
                } else {
                    return FluentBoardsImporter::process($importData);
                }
            }
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }


}