<?php

namespace FluentBoardsPro\App\Http\Policies;

use FluentBoards\Framework\Foundation\Policy;
use FluentBoards\Framework\Http\Request\Request;

class UserPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  WPFluent\Framework\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return true;
        return current_user_can('manage_options');
    }

    /**
     * Check user permission for any method
     * @param  WPFluent\Framework\Request\Request $request
     * @return Boolean
     */
    public function create(Request $request)
    {
        return current_user_can('manage_options');
    }

}
