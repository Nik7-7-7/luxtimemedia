<?php

namespace FluentBoardsPro\App\Http\Controllers;

use FluentBoards\Framework\Http\Request\Request;
use FluentBoardsPro\App\Services\PluginManager\LicenseManager;

class LicenseController extends \FluentBoards\App\Http\Controllers\Controller
{
    public function getStatus(Request $request, LicenseManager $licenseManager)
    {
       $licenseManager->verifyRemoteLicense(false);


        $data = $licenseManager->getLicenseDetails();

        $status = $data['status'];

        if ($status == 'expired') {
            $data['renew_url'] = $licenseManager->getRenewUrl($data['license_key']);
        }

        $data['purchase_url'] = $licenseManager->getVar('purchase_url');

        unset($data['license_key']);
        return $data;
    }

    public function saveLicense(Request $request, LicenseManager $licenseManager)
    {
        $licenseKey = $request->get('license_key');
        $response = $licenseManager->activateLicense($licenseKey);
        if (is_wp_error($response)) {
            return $this->sendError([
                'message' => $response->get_error_message()
            ]);
        }
        return [
            'license_data' => $response,
            'message'      => __('Your license key has been successfully updated', 'fluent-boards-pro')
        ];
    }

    public function deactivateLicense(Request $request, LicenseManager $licenseManager)
    {
        $response = $licenseManager->deactivateLicense();

        if (is_wp_error($response)) {
            return $this->sendError([
                'message' => $response->get_error_message()
            ]);
        }

        unset($response['license_key']);

        return [
            'license_data' => $response,
            'message'      => __('Your license key has been successfully deactivated', 'fluent-boards-pro')
        ];

    }
}
