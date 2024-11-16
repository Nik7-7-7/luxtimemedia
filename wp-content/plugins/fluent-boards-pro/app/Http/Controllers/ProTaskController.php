<?php

namespace FluentBoardsPro\App\Http\Controllers;

use FluentBoards\App\Models\Meta;
use FluentBoards\App\Models\Task;
use FluentBoards\App\Services\Helper;
use FluentBoards\Framework\Http\Request\Request;
use FluentBoards\Framework\Support\DateTime;
use FluentBoardsPro\App\Services\Constant;

class ProTaskController extends Controller
{
    public function createOrUpdateTaskRepeatMeta(Request $request, $board_id, $task_id)
    {
        // Validate and sanitize the repeat data from the request
        $repeatData = $this->validateAndSanitizeTaskRepeatData($request->all(), [
            'create_new'               => 'required',
            'repeat_in'                => 'required|integer',
            'repeat_type'              => 'required|string',
            'repeat_when_complete'     => 'required',
            'selected_month'           => 'required|string',
            'time'                     => 'required|string',
            'time_zone'                => 'required|string',
            'next_repeat_date'         => 'required|string',
            'repeat_in_month_type'     => 'required|string',
        ]);

        // Filter and validate selected repeat week days
        if (!empty($repeatData['selected_repeat_week_days'])) {
            $validDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            $repeatData['selected_repeat_week_days'] = array_filter($request->get('selected_repeat_week_days'), function($day) use ($validDays) {
                return in_array($day, $validDays);
            });
        }

        // Filter and validate selected month days
        if (!empty($request->get('selected_month_days'))) {
            $repeatData['selected_month_days'] = array_filter($request->get('selected_month_days'), function($day) {
                return is_numeric($day) && $day >= 1 && $day <= 31;
            });
        }

        // Remove unnecessary fields from repeat data
        unset($repeatData['rest_route']);
        unset($repeatData['task_id']);
        unset($repeatData['board_id']);
        unset($repeatData['query_timestamp']);

        try {
            // Find the task by ID
            $task = Task::find($task_id);

            // Combine date and time for the next repeat date
            $repeatData['next_repeat_date'] = $repeatData['next_repeat_date'] . ' ' . $repeatData['time'];
            $nextRepeat = new DateTime($repeatData['next_repeat_date'], new \DateTimeZone($repeatData['time_zone']));
            $serverTimeZone = new \DateTimeZone(date_default_timezone_get());
            $nextRepeat->setTimezone($serverTimeZone);
            $next_repeat_date_server = $nextRepeat->format('Y-m-d H:i:s');

            $message = __('Repeat task created successfully', 'fluent-boards-pro');

            // Update or create the repeat task meta
            if ($task->repeat_task_meta) {
                $task->repeat_task_meta->update([
                    'value' => $repeatData,
                    'key' => $next_repeat_date_server
                ]);
                $message = __('Repeat task update successfully', 'fluent-boards-pro');
            } else {
                Meta::create([
                    'object_id' => $task_id,
                    'object_type' => Constant::REPEAT_TASK_META,
                    'value' => $repeatData,
                    'key' => $next_repeat_date_server,
                ]);
            }

            // Return success response
            return $this->sendSuccess([
                'task' => $task,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            // Return error response
            return $this->sendError($e->getMessage());
        }
    }

    public function removeRepeatTaskMeta($board_id, $task_id)
    {
        try {
            // Find the repeat task meta by task ID and object type
            $repeatTaskMeta = Meta::where('object_id', $task_id)
                ->where('object_type', Constant::REPEAT_TASK_META)
                ->first();

            // Delete the repeat task meta if found
            if ($repeatTaskMeta) {
                $repeatTaskMeta->delete();
                return $this->sendSuccess([
                    'message' => __('Task repeat stopped successfully', 'fluent-boards-pro')
                ]);
            } else {
                return $this->sendError('Repeat task meta not found', 404);
            }
        } catch (\Exception $e) {
            // Return error response
            return $this->sendError($e->getMessage(), 400);
        }
    }

    private function validateAndSanitizeTaskRepeatData($data, array $rules = [])
    {
        // Sanitize the task repeat data
        $data = Helper::sanitizeTaskRepeatData($data);

        // Validate the sanitized data against the provided rules
        return $this->validate($data, $rules);
    }
}