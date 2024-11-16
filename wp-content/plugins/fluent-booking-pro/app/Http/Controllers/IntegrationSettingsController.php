<?php

namespace FluentBookingPro\App\Http\Controllers;

use FluentBooking\App\Http\Controllers\Controller;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBooking\App\Services\Integrations\Calendars\RemoteCalendarHelper;
use FluentBooking\Framework\Http\Request\Request;

class IntegrationSettingsController extends Controller
{
    public function index(Request $request, $hostId)
    {
        try {
            $settingsKey = sanitize_text_field($request->get('settings_key'));

            $settings = apply_filters('fluent_booking/get_integration_settings_' . $settingsKey, $hostId, []);

            $fieldSettings = apply_filters('fluent_booking/get_integration_field_settings_' . $settingsKey, $hostId, []);

            return $this->sendSuccess([
                'status'         => true,
                'settings'       => $settings,
                'field_settings' => $fieldSettings,
            ]);

        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function getIntegrationsMenu()
    {
        try {

            $baseUrl = Helper::getAppBaseUrl();
            $menuItems = apply_filters('fluent_booking/integrations_menu_items', [
                'google_calendar' => [
                    'key'       => 'google_calendar',
                    'label'     => __('Google Calendar', 'fluent-booking-pro'),
                    'svgIcon'   => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M2.5 7.59166V12.4C2.5 14.1667 2.5 14.1667 4.16667 15.2917L8.75 17.9417C9.44167 18.3417 10.5667 18.3417 11.25 17.9417L15.8333 15.2917C17.5 14.1667 17.5 14.1667 17.5 12.4083V7.59166C17.5 5.83333 17.5 5.83333 15.8333 4.70833L11.25 2.05833C10.5667 1.65833 9.44167 1.65833 8.75 2.05833L4.16667 4.70833C2.5 5.83333 2.5 5.83333 2.5 7.59166Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 12.5C11.3807 12.5 12.5 11.3807 12.5 10C12.5 8.61929 11.3807 7.5 10 7.5C8.61929 7.5 7.5 8.61929 7.5 10C7.5 11.3807 8.61929 12.5 10 12.5Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    'permalink' => $baseUrl . '/google_calendar'
                ]
            ]);

            return $this->sendSuccess([
                'status'     => true,
                'menu_items' => $menuItems
            ]);

        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function update(Request $request, $hostId)
    {
        try {
            $settingsKey = sanitize_text_field($request->get('settings_key'));

            $settings = wp_unslash($request->get('settings'));

            do_action('fluent_booking/save_integration_settings_' . $settingsKey, $settings, $hostId);

        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function revoke(Request $request, $hostId)
    {
        try {
            $settingsKey = sanitize_text_field($request->get('settings_key'));

            do_action('fluent_booking/disconnect_integration_' . $settingsKey, $hostId);

        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function getRemoteCalendars(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $providers = apply_filters('fluent_booking/remote_calendar_providers', [], $calendar->user_id, $calendar);
        $connectionFeeds = apply_filters('fluent_booking/remote_calendar_connection_feeds', [], $calendar->user_id, $calendar);

        return [
            'providers' => $providers,
            'feeds'     => $connectionFeeds,
            'settings'  => RemoteCalendarHelper::getUserRemoteCreatableCalendarSettings($calendar->user_id)
        ];
    }

    public function patchRemoteCalendarConflictSettings(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $meta = Meta::where('id', $request->get('meta_id'))->first();

        $conflictCheckIds = $request->get('conflict_check_ids');

        do_action('fluent_calendar/patch_calendar_config_settings_' . $meta->object_type, $conflictCheckIds, $meta, $calendar);

        return [
            'message' => __('Your settings has been updated', 'fluent-booking-pro')
        ];
    }

    public function patchRemoteCalendarAdditionalSettings(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $meta = Meta::where('id', $request->get('meta_id'))->first();

        $additionalSettings = wp_unslash($request->get('additional_settings'));

        do_action('fluent_calendar/patch_calendar_additional_settings_' . $meta->object_type, $additionalSettings, $meta, $calendar);

        return [
            'message' => __('Your settings has been updated', 'fluent-booking-pro')
        ];
    }

    public function syncCreatbleRemoteCalSettings(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $settings = $request->get('remote_calendar_config', []);

        RemoteCalendarHelper::updateUserRemoteCreatableCalendarSettings($calendar->user_id, $settings);

        return [
            'message' => __('Your settings has been updated', 'fluent-booking-pro')
        ];
    }

    public function disconnectRemoteCalendar(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $meta = Meta::where('id', $request->get('meta_id'))->first();
        $metaId = $meta->id;
        do_action('fluent_calendar/disconnect_remote_calendar_' . $meta->object_type, $meta, $calendar);
        do_action('fluent_booking/after_disconnect_remote_calendar', $metaId, $calendar);

        return [
            'message' => __('Your selected remote calendar has been disconnected', 'fluent-booking-pro')
        ];
    }

    public function addCalDavCredential(Request $request, $calendarId)
    {
        try {
            $calendar = Calendar::findOrFail($calendarId);
            $driverKey = sanitize_text_field($request->get('driver_key'));
            $result = apply_filters('fluent_booking/verify_save_caldav_credential_' . $driverKey, [
                'message' => __('Your credential could not be saved. Please make sure the credential is valid', 'fluent-booking-pro'),
                'success' => false
            ], $request->get('settings'), $calendar->user_id);

            if (empty($result['success'])) {
                return $this->sendError($result);
            }

            return $result;
        } catch (\Exception $e) {
            return $this->sendError([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function getGeneralIntegrationFeed(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $settingsKey = sanitize_text_field($request->get('settings_key'));

        $data = apply_filters('fluent_booking/get_general_integration_feed_' . $settingsKey, [], $calendar);

        if (!$data) {
            return $this->sendError([
                'message' => __('Integration Feed Settings could not be found. Driver missing!', 'fluent-booking-pro')
            ]);
        }

        return $data;
    }

    public function disconnectGeneralIntegrationFeed(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $settingsKey = sanitize_text_field($request->get('settings_key'));

        do_action('fluent_booking/disconnect_general_integration_feed_' . $settingsKey, $calendar);

        return [
            'message' => __('Your selected integration feed has been disconnected', 'fluent-booking-pro')
        ];
    }

}
