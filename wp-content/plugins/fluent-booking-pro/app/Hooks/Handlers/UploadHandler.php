<?php

namespace FluentBookingPro\App\Hooks\Handlers;

use FluentBooking\App\App;
use FluentBooking\Framework\Support\Arr;
use FluentBooking\App\Models\CalendarSlot;
use FluentBooking\App\Services\BookingFieldService;
use FluentBooking\App\Services\Libs\FileSystem;

class UploadHandler
{
    protected $request;

    public function __construct($app)
    {
        $this->request = $app->request;
        $this->register();
    }

    public function register()
    {
        add_action('wp_ajax_fluent_booking_file_upload', [$this, 'handleFileUpload']);
        add_action('wp_ajax_nopriv_fluent_booking_file_upload', [$this, 'handleFileUpload']);
        add_action('wp_ajax_fluent_booking_file_delete', [$this, 'handleFileDelete']);
        add_action('wp_ajax_nopriv_fluent_booking_file_delete', [$this, 'handleFileDelete']);
    }

    public function handleFileUpload()
    {
        $data = Arr::except($this->request->all(), ['action']);

        $files = $this->request->files();

        $eventId = Arr::get($data, 'event_id');

        $fieldName = Arr::get($data, 'field_name');

        $calendarEvent = CalendarSlot::find($eventId);

        if (!$calendarEvent) {
            wp_send_json([
                'errors' =>  __('Calendar Event not found', 'fluent-booking-pro')
            ], 422);
        }

        $fieldSettings = BookingFieldService::getBookingFieldByName($calendarEvent, $fieldName);

        if (!$fieldSettings) {
            wp_send_json([
                'errors' =>  __('Field not found', 'fluent-booking-pro')
            ], 422);
        }

        $maxFileUnit = Arr::get($fieldSettings, 'max_file_unit', 'kb');
        $maxFileValue = Arr::get($fieldSettings, 'max_file_value', 14);
        $maxFileSize = $maxFileValue * ($maxFileUnit == 'mb' ? (1024 * 1024) : 1024);
        $allowFileTypes = Arr::get($fieldSettings, 'allow_file_types', []);

        if (in_array('image', $allowFileTypes)) {
            $imageTypes = ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'webp'];
            $allowFileTypes = array_merge($allowFileTypes, $imageTypes);
        }

        $app = App::getInstance();

        $validationConfig = apply_filters('fluent_booking/file_upload_validation_rules_data', [
            'rules'    => [
                'file' => 'max:'.$maxFileSize.'|mimes:'.implode(',', $allowFileTypes)
            ],
            'messages' => [
                'file.max'   => __('Validation fails for maximum file size', 'fluent-booking-pro'),
                'file.mimes' => __('Allowed image types does not match', 'fluent-booking-pro')
            ]
        ], $calendarEvent, $fieldName, $files);

        $validator = $app->validator->make($files, $validationConfig['rules'], $validationConfig['messages']);

        if ($validator->validate()->fails()) {
            wp_send_json([
                'errors'  => $validator->errors()
            ], 422);
        }

        $uploadedFiles = FileSystem::put($files);

        $file = $uploadedFiles[0];

        return wp_send_json([
            'file' => $file
        ]);
    }

    public function handleFileDelete()
    {
        $file = Arr::get($this->request->all(), 'file');

        FileSystem::delete($file);

        return wp_send_json([
            'message' => __('File deleted successfully', 'fluent-booking-pro')
        ]);
    }
}
