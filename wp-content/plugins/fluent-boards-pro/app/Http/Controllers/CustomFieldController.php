<?php

namespace FluentBoardsPro\App\Http\Controllers;

use FluentBoards\App\Services\Helper;
use FluentBoards\Framework\Http\Request\Request;
use FluentBoardsPro\App\Services\CustomFieldService;

class CustomFieldController extends Controller
{
    public function getCustomFields($board_id)
    {
        $customFieldService = new CustomFieldService();

        $customFields =  $customFieldService->getCustomFields($board_id);

        return $this->sendSuccess([
            'customFields' => $customFields,
        ], 200);
    }

    public function createCustomField(Request $request, $board_id)
    {
        $customFieldData = $this->boardSanitizeAndValidate($request->get('customField'), [
            'title'       => 'required|string',
            'type'        => 'required|string'
        ]);

        $customFieldService = new CustomFieldService();
        $customField = $customFieldService->createCustomField($board_id, $customFieldData);

        if(!$customField)
        {
            return $this->sendError([
               'message' => __('Custom Field with that title and type already exists', 'fluent-boards-pro'),
            ], 400);
        }

        return $this->sendSuccess([
            'customField' => $customField,
            'message'     => __('Custom field has been successfully created', 'fluent-boards-pro'),
        ], 201);
    }

    public function  updateCustomField(Request $request, $board_id, $custom_field_id)
    {
        $customFieldData = $this->boardSanitizeAndValidate($request->get('customField'), [
            'title'       => 'required|string',
            'type'        => 'required|string'
        ]);

        $customFieldService = new CustomFieldService();
        $customField = $customFieldService->updateCustomField($custom_field_id, $customFieldData);

        return $this->sendSuccess([
            'customField' => $customField,
            'message'     => __('Custom field has been updated successfully', 'fluent-boards-pro'),
        ], 201);
    }

    public function deleteCustomField($board_id, $custom_field_id)
    {
        $customFieldService = new CustomFieldService();
        $customFieldService->deleteCustomField($custom_field_id);

        return $this->sendSuccess([
            'message'     => __('Custom field has been deleted successfully', 'fluent-boards-pro'),
        ], 201);
    }

    public function getCustomFieldsByTask($board_id, $task_id)
    {
        $customFieldService = new CustomFieldService();

        $customFields =  $customFieldService->getCustomFieldsByTask($task_id);

        return $this->sendSuccess([
            'customFields' => $customFields,
        ], 200);
    }

    public function saveCustomFieldDataOfTask(Request $request, $board_id, $task_id)
    {
        $customFieldId = $request->getSafe('custom_field_id');
        $value = $request->getSafe('value');

        $customFieldService = new CustomFieldService();

        $customField =  $customFieldService->saveCustomFieldDataOfTask($task_id, $customFieldId, $value);

        return $this->sendSuccess([
            'customField' => $customField,
        ], 201);
    }

    private function boardSanitizeAndValidate($data, array $rules = [])
    {
        $data = Helper::sanitizeBoard($data);

        return $this->validate($data, $rules);
    }
}