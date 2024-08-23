<?php

namespace app\controller;


use support\Response;

class IndexController
{

    public function index(): Response
    {
        return view('index/index');
    }

}
