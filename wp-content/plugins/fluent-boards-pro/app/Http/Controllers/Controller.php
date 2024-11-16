<?php

namespace FluentBoardsPro\App\Http\Controllers;

use FluentBoardsPro\App\Core\App;
use FluentBoards\App\Http\Controllers\Controller as BaseController;

abstract class Controller extends BaseController
{
    public function __construct()
    {
        parent::__construct(App::getInstance());
    }
}
