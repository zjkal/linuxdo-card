<?php

namespace app\controller;

use Intervention\Image\ImageManagerStatic as Image;
use QL\QueryList;
use support\Request;
use support\Response;
use think\facade\Cache;
use zjkal\TimeHelper;

class IndexController
{

    public function index(): string
    {
        return 'Hello World!';
    }

}
