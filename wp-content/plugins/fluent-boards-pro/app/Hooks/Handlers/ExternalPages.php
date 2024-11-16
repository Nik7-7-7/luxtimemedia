<?php

namespace FluentBoardsPro\App\Hooks\Handlers;

use FluentBoards\App\Models\Attachment;
use FluentBoards\App\Models\Meta;
use FluentBoards\App\Models\Stage;
use FluentBoards\App\Models\Webhook;
use FluentBoards\App\Services\Constant;
use FluentBoards\App\Services\Libs\FileSystem;
use FluentBoards\Framework\Support\Arr;
use FluentBoardsPro\App\Core\App as ProApp;
use FluentBoardsPro\App\Models\TaskAttachment;


class ExternalPages
{
    protected $request;
    public function boardMemberInvitation()
    {
        $boardId = $_GET['bid'];
        $email = $_GET['email'];
        $hashCode = $_GET['hash'];
        $activeHashCodes = $this->getActiveHashCodes($boardId);

        $app = ProApp::getInstance();

        foreach ($activeHashCodes as $hashCodes) {
            $value = maybe_unserialize($hashCodes->value);
            if($value['email'] == $email && $value['hash'] == $hashCode){
                $app->view->render('register_member_form', [
                    'boardId' => $boardId,
                    'email' => $email,
                    'hash' => $hashCode,
                ]);
                exit();
            }
        }
    }
    private function getActiveHashCodes($boardId)
    {
        return Meta::query()->where('object_id', $boardId)
            ->where('object_type', Constant::OBJECT_TYPE_BOARD)
            ->where('key', Constant::BOARD_INVITATION)
            ->get();
    }

    public function view_attachment()
    {
        $attachmentHash = sanitize_text_field($_REQUEST['fbs_attachment']);

        if (empty($attachmentHash)) {
            die('Invalid Attachment Hash');
        }

        $attachment = $this->getAttachmentByHash($attachmentHash);

        if (in_array($attachment->object_type, [Constant::TASK_ATTACHMENT])) {
            $attachment->load('task');
        }

        if (!$attachment) {
            die('Invalid Attachment Hash');
        }

        // check signature hash
        if (!$this->validateAttachmentSignature($attachment)) {
            $dieMessage = __('Sorry, Your secure sign is invalid, Please reload the previous page and get new signed url', 'fluent-support');
            die($dieMessage);
        }

        //If external file
        if ('local' !== $attachment->driver) {
            if(!empty($attachment->file_path)){
                $this->redirectToExternalAttachment($attachment->full_url);
            }else{
                die('File could not be found');
            }
        }

        //Handle Local file
        if (in_array($attachment->object_type, [Constant::TASK_ATTACHMENT])) {
            $fileName = $attachment->file_path;
            $boardId = $attachment->task->board_id;
        }

        $filePath = $fileName;
        if(!file_exists($fileName)){
            $filePath = FileSystem::setSubDir('board_' . $boardId)->getDir() . DIRECTORY_SEPARATOR . $fileName;
        }

        if (!file_exists($filePath)) {
            die('File could not be found.');
        }

        $this->serveLocalAttachment($attachment, $filePath);
    }

    private function getAttachmentByHash($attachmentHash)
    {
        return TaskAttachment::where('file_hash', $attachmentHash)->first();
    }

    private function validateAttachmentSignature($attachment)
    {
        $sign = md5($attachment->id . date('YmdH'));
        return $sign === $_REQUEST['secure_sign'];
    }

    /*
     * Showing local attachment
    */
    private function serveLocalAttachment($attachment, $filePath)
    {
        ob_get_clean();
        header("Content-Type: {$attachment->attachment_type}");
        header("Content-Disposition: inline; filename=\"{$attachment->title}\"");;
        echo readfile($filePath);
        die();
    }

    private function redirectToExternalAttachment($redirectUrl)
    {
        wp_redirect($redirectUrl, 307);
        exit();
    }

    public function handleTaskWebhook()
    {
        $this->request = FluentBoards('request');

        // check if it's a POST request
        if ($this->request->method() != 'POST') {
            wp_send_json_error([
                'message' => __('Webhook must need to be as POST Method', 'fluent-boards'),
                'type'    => 'invalid_request_method'
            ], 200);
        }

        if (empty($hash = $this->request->get('hash'))) {
            wp_send_json_error([
                'message' => __('Invalid Webhook URL', 'fluent-crm'),
                'type'    => 'invalid_webhook_url'
            ], 200);
        }

        $webhook = $this->getWebhookByHash($hash);
        if (!$webhook) {
            wp_send_json_error([
                'message' => __('Invalid Webhook Hash', 'fluent-boards'),
                'type'    => 'invalid_webhook_hash'
            ], 200);
        }

        $postData = $this->request->get();

        if(empty($postData['title'])){
            wp_send_json_error([
                'message' => __('Task Title is required', 'fluent-boards'),
                'type'    => 'task_title_required'
            ], 200);
        }

        $postData = apply_filters('fluent_boards/incoming_webhook_data', $postData, $webhook);

        // Set default values in the first place
        $boardId = Arr::get($webhook->value, 'board');
        $stageId = Arr::get($webhook->value, 'stage');

        // Check if stage is provided in postData
        if (!empty($postData['stage'])) {
            $stage = Stage::where(function ($query) use ($postData) {
                $query->where('title', $postData['stage'])
                      ->orWhere('slug', $postData['stage']);
            })
                          ->where('board_id', $boardId)
                          ->first();
            if ($stage) {
                $stageId = $stage->id;
            }
        }

//        // Check again for stage and board match
//        $stageExists = Stage::where('id', $stageId)->where('board_id', $boardId)->first();
//        if (!$stageExists) {
//            wp_send_json_error([
//                'message' => __('Stage or Board Mismatched', 'fluent-boards'),
//                'type'    => 'invalid_stage_or_board'
//            ], 200);
//        }

        $data = $postData;
        $data['board_id'] = $boardId;
        $data['stage_id'] = $stageId;

        $data = apply_filters('fluent_boards/webhook_task_data', $data, $webhook);

        $task = FluentBoardsApi('tasks')->create($data);

        if(!$task){
            wp_send_json_error([
                'message' => __('Task Creation Failed', 'fluent-boards'),
                'type'    => 'task_creation_failed'
            ], 400);
        }
        wp_send_json_success([
           'message' => __('Task Created Successfully', 'fluent-boards'),
            'task'    => $task
        ], 200);
    }

    public function getWebhookByHash($hash)
    {
        return Webhook::where('key', $hash)->first();
    }

}