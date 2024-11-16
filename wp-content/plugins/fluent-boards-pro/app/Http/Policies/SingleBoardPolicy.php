<?php

namespace FluentBoardsPro\App\Http\Policies;


use FluentBoards\Framework\Http\Request\Request;

class SingleBoardPolicy extends Policy
{
    /**
     * Check user permission for any method
     * @param  WPFluent\Framework\Request\Request $request
     * @return Boolean
     */
    public function verifyRequest(Request $request)
    {
        return true;
    }
}
