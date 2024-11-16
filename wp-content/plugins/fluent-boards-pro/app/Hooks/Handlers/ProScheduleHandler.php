<?php

namespace FluentBoardsPro\App\Hooks\Handlers;

use FluentBoards\App\Models\Meta;
use FluentBoards\App\Models\Relation;
use FluentBoards\App\Models\Task;
use FluentBoards\App\Services\Constant;
use FluentBoards\App\Services\Helper;
use FluentBoards\App\Services\PermissionManager;
use FluentBoards\Framework\Support\Arr;

class ProScheduleHandler
{
    public function hourlyScheduler()
    {
        $this->scheduleDailyTaskReminder();
    }

    public function recurringTaskScheduler()
    {
        $modules = fluent_boards_get_pref_settings(false);
        if($modules['recurring_task']['enabled'] == 'yes' && !as_next_scheduled_action('fluent_boards/repeat_task_scheduler')) {
            as_schedule_recurring_action(time() + MINUTE_IN_SECONDS * 5, MINUTE_IN_SECONDS * 5, 'fluent_boards/repeat_task_scheduler', [], 'fluent-boards');
        }
    }

    public function scheduleDailyTaskReminder()
    {
        $this->schedule();
    }
    public function schedule()
    {
        $remind_at = Arr::get(fluent_boards_get_option('general_settings'), 'daily_remind_at', '08:00'); // Default time is 8:00 AM
        $is_reminder_enabled = Arr::get(fluent_boards_get_option('general_settings'), 'daily_reminder_enabled') == 'true' ? true : false;

        if ( ! as_next_scheduled_action('fluent_boards/daily_task_reminder') && $is_reminder_enabled) {

            $currentTime = current_time('timestamp');
            $localTimeZone = new \DateTimeZone(wp_timezone_string());
            $localDateTime = new \DateTime('now', $localTimeZone);
            $utcOffsetInSeconds = $localDateTime->getOffset();

            // Convert $remind_at to a timestamp for the current day
            $timeAtReminderLocal = strtotime(date('Y-m-d', $currentTime) . ' ' . $remind_at);
            $timeAtReminderUTC = $timeAtReminderLocal - $utcOffsetInSeconds;

            if ($currentTime >= $timeAtReminderLocal) {
                // If current time is greater than or equal to the reminder time, schedule it for the next day at the reminder time
                $nextRunDateTimeLocal = strtotime('tomorrow ' . $remind_at, $currentTime);
                $nextRunDateTimeUTC = $nextRunDateTimeLocal - $utcOffsetInSeconds;
            } else {
                // Schedule it for today at the reminder time
                $nextRunDateTimeUTC = $timeAtReminderUTC;
            }

            // Schedule the single action
            as_schedule_single_action($nextRunDateTimeUTC, 'fluent_boards/daily_task_reminder', [], 'fluent-boards');

        }
    }

    /**
     * Clear all the scheduled actions for Fluent Boards on deactivation
     * @return void
     */
    public function clear_All_FBS_Scheduler()
    {
        $this->clearDailyTaskReminderScheduler();
        $this->clearHourlyScheduler();
        $this->clearRepeatTaskScheduler();
    }

    /**
     * Clear the daily task reminder scheduler
     * @return void
     */
    public function clearDailyTaskReminderScheduler()
    {
        if(as_next_scheduled_action('fluent_boards/daily_task_reminder')) {
            as_unschedule_action('fluent_boards/daily_task_reminder');
        }
    }

    /**
     * clear the hourly scheduler
     * @return void
     */
    public function clearHourlyScheduler()
    {
        if(as_next_scheduled_action('fluent_boards/hourly_scheduler')) {
            as_unschedule_action('fluent_boards/hourly_scheduler');
        }
    }

    public function clearRepeatTaskScheduler()
    {
        if(as_next_scheduled_action('fluent_boards/repeat_task_scheduler')) {
            as_unschedule_action('fluent_boards/repeat_task_scheduler');
        };
    }

    /**
     * @throws \Exception
     */
    public function dailyTaskSummaryMail()
    {
        if ( ! function_exists('fluentBoards')) {
            return;
        }

        $is_reminder_enabled = Arr::get(fluent_boards_get_option('general_settings'), 'daily_reminder_enabled') == 'true' ? true : false;
        if (! $is_reminder_enabled) {
            return;
        }

        // check if it ran today. If yes, then do not run again
        $lastRunDateTime = fluent_boards_get_option('_last_daily_summary_run_at');
        if ($lastRunDateTime) {
            if (date('Ymd') == date('Ymd', strtotime($lastRunDateTime))) {
                return false; // We don't want to run this at the same date
            }
        }

        // Get all the tasks which are due today and not completed yet
        $startTime = date('Y-m-d 00:00:00', current_time('timestamp'));
        $endTime   = date('Y-m-d 23:59:59', current_time('timestamp'));

        $tasks = Task::whereBetween('due_at', [$startTime, $endTime])
                     ->where('last_completed_at', null)
                     ->get();

        // if no tasks are due today, then do not send mail
        if ( ! $tasks) {
            fluent_boards_update_option('_last_daily_summary_userid', 0);
            fluent_boards_update_option('_last_daily_summary_run_at', date('Y-m-d H:i:s'));
            return;
        }

        $allDueTaskIds = $tasks->pluck('id')->toArray();

        $tasksAndWatchersQuery = Relation::where('object_type', Constant::OBJECT_TYPE_USER_TASK_WATCH)
                                             ->whereIn('object_id', $allDueTaskIds)
                                             ->orderBy('foreign_id', 'ASC');

        $lastSentId = fluent_boards_get_option('_last_daily_summary_userid');
        if ($lastSentId) {
            //  get the tasks and watchers which are greater than the last sent id (user id) to send the mail to the next user
            $tasksAndWatchersQuery = $tasksAndWatchersQuery->where('foreign_id', '>', $lastSentId);
        }

        // get all the people who are watching those tasks
        $userIds = $tasksAndWatchersQuery->pluck('foreign_id')->toArray();
        // make userId unique
        $userIds = array_unique($userIds);

        if ( ! $userIds) {
            // Completed for this day as no user is watching any task for this day
            // set the last id as 0
            fluent_boards_update_option('_last_daily_summary_userid', 0);
            // set the last run as today
            fluent_boards_update_option('_last_daily_summary_run_at', date('Y-m-d H:i:s'));
            return;
        }

        $processingStartTime = time();
        $hasMoreUsers        = false;

        foreach ($userIds as $userId) {
            // get all the tasks which are watched by this user
            $taskIds = PermissionManager::getTaskIdsWatchByUser($userId);
            // filter the tasks which are due today and not completed yet
            $perUserTasks = $tasks->filter(function ($task) use ($taskIds) {
                return in_array($task->id, $taskIds);
            });

            $this->sendDailyTaskSummaryToUser($userId, $perUserTasks);

            if (time() - $processingStartTime > 40) { // if it takes more than 40 seconds, then break the loop
                // set the last id as this user id
                $hasMoreUsers = true;
                fluent_boards_update_option('_last_daily_summary_userid', $userId);
                // schedule a one time action scheduler to run this function again
                as_schedule_single_action(time() + 20, 'fluent_boards/daily_task_reminder', [], 'fluent-boards'); // run this function again after 10 seconds
                break;
            }
        }

        if ( ! $hasMoreUsers) {
            // Completed for this day as no user left to send the mail
            // set the last id as 0
            fluent_boards_update_option('_last_daily_summary_userid', 0);
            // set the last run as today
            fluent_boards_update_option('_last_daily_summary_run_at', date('Y-m-d H:i:s'));
        }
    }

    public function sendDailyTaskSummaryToUser($userId, $tasks)
    {
        $headers  = ['Content-Type: text/html; charset=UTF-8'];
        $user     = get_user_by('ID', $userId);
        $subject  = __('Your Daily Task Summary - Stay on Track',
            'fluent-boards');
        $page_url = fluent_boards_page_url();
        $to       = $user->user_email;
        $name     = $user->display_name;

        $data = [
            'tasks'       => $tasks,
            'name'        => $name,
            'page_url'    => $page_url,
            'pre_header'  => __('Daily Digest Mail', 'fluent-boards'),
            'show_footer' => true,
            'site_url'    => site_url(),
            'site_title'  => get_bloginfo('name'),
            'site_logo'   => fluent_boards_site_logo(),
        ];

        $message = Helper::loadView('emails.daily',
            $data); // view is loaded from fluent-boards plugin file

        return \wp_mail($to, $subject, $message, $headers);

    }

    /**
     * Repeat tasks scheduler
     */
    public function repeatTasks()
    {
        $dateTime= new \DateTime('now', new \DateTimeZone(date_default_timezone_get()));
        $formattedTime = $dateTime->format('Y-m-d H:i:s');

        // Fetch repeat tasks meta using UTC time
        $repeatTasksMeta = Meta::where('object_type', \FluentBoardsPro\App\Services\Constant::REPEAT_TASK_META)
            ->where('key', '<=', $formattedTime)
            ->get();

        foreach ($repeatTasksMeta as $repeatTaskMeta) {
            do_action('fluent_boards/repeat_task', $repeatTaskMeta->object_id);
        }

    }
}