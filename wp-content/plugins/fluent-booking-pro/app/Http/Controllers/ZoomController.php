<?php

namespace FluentBookingPro\App\Http\Controllers;

use FluentBooking\App\Http\Controllers\Controller;
use FluentBooking\App\Models\Calendar;
use FluentBooking\App\Models\Meta;
use FluentBooking\App\Services\Helper;
use FluentBookingPro\App\Services\Integrations\ZoomMeeting\Client;
use FluentBookingPro\App\Services\Integrations\ZoomMeeting\ZoomHelper;
use FluentBooking\App\Services\PermissionManager;
use FluentBooking\Framework\Http\Request\Request;
use FluentBooking\Framework\Support\Arr;

class ZoomController extends Controller
{
    public function get(Request $request)
    {
        $metaItems = Meta::where('object_type', 'user_meta')
            ->where('key', 'zoom_credentials')
            ->get();

        $formattedItems = [];
        foreach ($metaItems as $metaItem) {
            $user = get_user_by('ID', $metaItem->object_id);
            if ($user) {
                $name = trim($user->first_name . ' ' . $user->last_name);
                if (!$name) {
                    $name = $user->display_name;
                }
                $userData = [
                    'user_id'    => $user->ID,
                    'name'       => $name,
                    'user_email' => $user->user_email,
                    'avatar'     => Helper::fluentBookingUserAvatar($user->user_email, $user)
                ];
            } else {
                $userData = [
                    'user_id'    => $metaItem->object_id,
                    'name'       => __('Deleted user', 'fluent-booking-pro'),
                    'user_email' => ''
                ];
            }

            $zoomUserEmail = Arr::get($metaItem->value, 'origin_email');

            $formattedItems[] = [
                'id'              => $metaItem->id,
                'owner'           => $userData,
                'zoom_email'      => $zoomUserEmail,
                'created_at'      => $metaItem->created_at,
                'zoom_account_id' => Arr::get($metaItem->value, 'account_id'),
            ];
        }

        return [
            'zoom_users'                 => $formattedItems,
            'is_current_user_configured' => ZoomHelper::isZoomConfigured(get_current_user_id()),
            'form_fields'                => ZoomHelper::getZoomConnectionFields()
        ];
    }

    public function save(Request $request)
    {
        $info = $request->get('zoom_credentials');

        $userId = $request->get('user_id');

        if (!$userId) {
            $userId = get_current_user_id();
        }

        $exist = Meta::where('object_type', 'user_meta')
            ->where('key', 'zoom_credentials')
            ->where('object_id', $userId)
            ->first();

        if ($exist) {
            return $this->sendError([
                'message' => __('Zoom credentials already exist for this user', 'fluent-booking-pro'),
            ]);
        }

        $credentials = $this->validate($info, [
            'account_id'    => 'required',
            'client_id'     => 'required',
            'client_secret' => 'required',
        ]);

        // Validate the account ID
        $client = new Client(
            $credentials['client_id'],
            $credentials['client_secret'],
            $credentials['account_id']
        );

        $accessToken = $client->generateAccessToken();

        if (is_wp_error($accessToken)) {
            return $this->sendError([
                'message' => $accessToken->get_error_message()
            ]);
        }

        $credentials['access_token'] = $accessToken['access_token'];
        $credentials['expires_at'] = $accessToken['expires_in'] + time();

        $client = $client->setAccessToken($accessToken['access_token']);

        $me = $client->me();

        if (is_wp_error($me)) {
            return $this->sendError([
                'message' => $me->get_error_message()
            ]);
        }

        if (empty($me['email'])) {
            return $this->sendError([
                'message' => __('Zoom account email is empty', 'fluent-booking-pro')
            ]);
        }

        $credentials['origin_email'] = $me['email'];
        $credentials['origin_display_name'] = $me['display_name'];

        ZoomHelper::updateZoomCredentials($userId, $credentials);

        return $this->sendSuccess([
            'message' => __('Zoom credentials has been validated and saved securely', 'fluent-booking-pro')
        ]);
    }

    public function addConnectionByCalendarId(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $request->set('user_id', $calendar->user_id);

        return $this->save($request);
    }

    public function disconnectByCalendarId(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $userId = $calendar->user_id;

        $connectionInfo = $this->getUserInfo($request, $userId);
        $request->set('connected_id', $connectionInfo['id']);

        return $this->disconnectByConnectId($request);
    }

    public function disconnectByConnectId(Request $request)
    {
        $connectedId = $request->get('connected_id');
        $metaItem = Meta::where('object_type', 'user_meta')
            ->where('key', 'zoom_credentials')
            ->where('id', $connectedId)
            ->first();

        if (!$metaItem) {
            return $this->sendError([
                'message' => __('Zoom credentials not found', 'fluent-booking-pro')
            ]);
        }

        $client = ZoomHelper::getZoomClient($metaItem->object_id);

        if(!is_wp_error($client)) {
            $response = $client->revokeConnection();
        } else {
            $response = $client;
        }

        $metaItem->delete();

        return [
            'message'  => __('Zoom credentials has been disconnected & deleted', 'fluent-booking-pro'),
            'response' => $response
        ];
    }

    public function getZoomConnectionByCalendarId(Request $request, $calendarId)
    {
        $calendar = Calendar::findOrFail($calendarId);
        $userId = $calendar->user_id;
        $userInfo = $this->getUserInfo($request, $userId);

        if ($userInfo) {
            return [
                'connection' => $userInfo
            ];
        }

        return [
            'connection'  => $userInfo,
            'form_fields' => ZoomHelper::getZoomConnectionFields()
        ];
    }

    public function me(Request $request)
    {
        return $this->getUserInfo($request, 0);
    }

    public function getUserInfo(Request $request, $userId = 0)
    {
        if (!$userId) {
            $userId = get_current_user_id();
        } else if (!PermissionManager::hasAllCalendarAccess()) {
            $currentUserId = get_current_user_id();
            if ($currentUserId != $userId) {
                $this->sendError([
                    'message' => __('You do not have permission to access this user\'s Zoom credentials', 'fluent-booking-pro'),
                    'type'    => 'no_permission'
                ]);
            }
        }

        $metaItem = Meta::where('object_type', 'user_meta')
            ->where('key', 'zoom_credentials')
            ->where('object_id', $userId)
            ->first();

        if (!$metaItem) {
            return null;
        }

        $user = get_user_by('ID', $metaItem->object_id);

        if (!$user) {
            return null;
        }

        $name = trim($user->first_name . ' ' . $user->last_name);
        if (!$name) {
            $name = $user->display_name;
        }

        return [
            'id'              => $metaItem->id,
            'owner'           => [
                'user_id'    => $user->ID,
                'name'       => $name,
                'user_email' => $user->user_email,
                'avatar'     => get_avatar_url($user->user_email)
            ],
            'zoom_email'      => Arr::get($metaItem->value, 'origin_email'),
            'created_at'      => $metaItem->created_at,
            'zoom_account_id' => Arr::get($metaItem->value, 'account_id'),
        ];
    }
}
