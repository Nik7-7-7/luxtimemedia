<?php

namespace FluentBoardsPro\App\Http\Policies;

use FluentBoards\Framework\Foundation\Policy as BasePolicy;
use FluentBoards\Framework\Http\Request\Request;

class Policy extends BasePolicy
{
    public function verifyRequest(Request $request)
    {
        return true;
    }

}