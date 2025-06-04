<?php

namespace app\controller;

use GuzzleHttp\Client;

class TestController
{
    public function index()
    {
        return view('test/index');
    }

    public function test(): string
    {
        $client = new Client();
        $response = $client->get('https://linux.do/u/zjkal/summary.json', [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0',
            ]
        ]);
        return $response->getBody()->getContents();
    }

}