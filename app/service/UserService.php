<?php

namespace app\service;

use QL\QueryList;
use think\exception\InvalidArgumentException;
use think\facade\Cache;

class UserService
{
    public static function getUserInfo($username)
    {
        if (empty($username)) {
            throw new InvalidArgumentException('username cannot be empty');
        }
        //获取用户数据
        if (empty($data = Cache::get("user_data_{$username}"))) {
            $json = QueryList::get("https://linux.do/u/{$username}/summary.json")->getHtml();
            $summary = json_decode($json, true);

            $json = QueryList::get("https://linux.do/u/{$username}.json")->getHtml();
            $user = json_decode($json, true);

            //收集数据
            $data = [
                'username'         => $user['user']['username'] ?? '',
                'trust_level'      => $user['user']['trust_level'] ?? '',
                'created_at'       => $user['user']['created_at'] ?? '',
                'last_seen_at'     => $user['user']['last_seen_at'] ?? '',
                'bio_excerpt'      => $user['user']['bio_excerpt'] ?? '',
                'days_visited'     => $summary['user_summary']['days_visited'] ?? '',
                'time_read'        => $summary['user_summary']['time_read'] ?? '',
                'topics_entered'   => $summary['user_summary']['topics_entered'] ?? '',
                'posts_read_count' => $summary['user_summary']['posts_read_count'] ?? '',
                'likes_given'      => $summary['user_summary']['likes_given'] ?? '',
                'likes_received'   => $summary['user_summary']['likes_received'] ?? '',
                'post_count'       => $summary['user_summary']['post_count'] ?? '',
                'solved_count'     => $summary['user_summary']['solved_count'] ?? '',
            ];

            //过滤签名中的HTML标签
            $data['bio_excerpt'] = strip_tags($data['bio_excerpt']);
            //过滤换行
            $data['bio_excerpt'] = str_replace(["\r", "\n"], '', $data['bio_excerpt']);

            //缓存数据
            Cache::set("user_data_{$username}", $data, 600);
        }

        //返回数据
        return $data;
    }
}