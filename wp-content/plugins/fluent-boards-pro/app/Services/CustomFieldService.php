<?php

namespace FluentBoardsPro\App\Services;

use FluentBoards\App\Models\Meta;
use FluentBoards\App\Models\Relation;
use FluentBoards\Framework\Support\DateTime;
use FluentBoardsPro\App\Models\CustomField;

class CustomFieldService
{
    public function getCustomFields($boardId)
    {
        $customFields = CustomField::where('board_id', $boardId)->get();
        return $customFields;
    }

    public function createCustomField($boardId, $customFieldData)
    {
        if($this->duplicateCustomFieldcheck($boardId, $customFieldData)){
            return false;
        }
        $customField = new CustomField();
        $customField->board_id = $boardId;
        $customField->title = $customFieldData['title'];
        $customField->slug = str_replace(' ', '-', strtolower($customFieldData['title']));

        $preferenceData = [];
        $preferenceData['custom_field_type'] = $customFieldData['type'];
        if ( key_exists('options', $customFieldData) )
        {
            $preferenceData['select_options'] = $customFieldData['options'];
        }
        $customField->settings = $preferenceData;
        $customField->save();

        return $customField;
    }

    public function updateCustomField($customFieldId, $customFieldData)
    {
        $customField = CustomField::findOrFail($customFieldId);

        if($this->duplicateCustomFieldcheck($customField->board_id, $customFieldData, $customField->id)){
            return false;
        }
        $customField->title = $customFieldData['title'];

        $preferenceData = [];
        if ( key_exists('options', $customFieldData) )
        {
            $preferenceData['select_options'] = $customFieldData['options'];
        }
        $customField->settings = $preferenceData;
        $customField->save();

        return $customField;
    }

    private function duplicateCustomFieldcheck($boardId, $customFieldData, $customFieldId = null)
    {
        $isDuplicate = false;
        $customFields = CustomField::where('type', 'custom-field')
            ->where('board_id', $boardId)
            ->where('title', $customFieldData['title'])
            ->get();

        foreach ($customFields as $customField)
        {
            if ( ($customFieldId != $customField->id) && $customField->settings['custom_field_type'] == $customFieldData['type'])
            {
                $isDuplicate = true;
                break;
            }
        }

        return $isDuplicate;
    }

    public function deleteCustomField($customFieldId)
    {
        $customField = CustomField::findOrFail($customFieldId);
        if($customField)
        {
            $deleted = $customField->delete();
            if($deleted)
            {
                $customField->tasks()->detach();
            }
        }
    }

    public function getCustomFieldsByTask($taskId)
    {
        return Relation::where('object_id', $taskId)
            ->where('object_type', Constant::TASK_CUSTOM_FIELD)
            ->get();
    }

    public function getCustomFieldById($id)
    {
        return customField::findOrFail($id);
    }

    public function saveCustomFieldDataOfTask($taskId, $customFieldId, $value)
    {
        $customField = $this->getCustomFieldById($customFieldId);
        $customFieldOfTask = Relation::where('object_id', $taskId)
            ->where('object_type', Constant::TASK_CUSTOM_FIELD)
            ->where('foreign_id', $customFieldId)
            ->first();

        if ($customFieldOfTask) {
            if ($customField->settings['custom_field_type'] == 'checkbox') {
                $value = $value == 'true' ? true : false;
            } else if ($customField->settings['custom_field_type'] == 'date') {
               $value = $this->formatDate($value);
            }
            $customFieldOfTask->settings = ['value' => $value];
            $customFieldOfTask->save();
        } else {
            $customFieldOfTask = new Relation();
            $customFieldOfTask->object_id = $taskId;
            $customFieldOfTask->object_type = Constant::TASK_CUSTOM_FIELD;
            $customFieldOfTask->foreign_id = $customFieldId;
            if ($customField->settings['custom_field_type'] == 'checkbox') {
                $value = $value == 'true' ? true : false;
            } else if ($customField->settings['custom_field_type'] == 'date') {
                $value = $this->formatDate($value);
            }
            $customFieldOfTask->settings = ['value' => $value];
            $customFieldOfTask->save();
        }

        return $customField;
    }

    private function formatDate($value)
    {
        // Remove the timezone name, it was creating formating issue
        $dateStringWithoutTimezoneName = preg_replace('/ \(.*\)$/', '', $value);
        $date = DateTime::createFromFormat('D M d Y H:i:s \G\M\TO', $dateStringWithoutTimezoneName);
        return $date->format('Y-m-d H:i:s');
    }
}