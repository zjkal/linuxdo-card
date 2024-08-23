<?php

namespace app\controller;

use Intervention\Image\ImageManagerStatic as Image;
use QL\QueryList;
use support\Request;
use support\Response;
use think\facade\Cache;
use zjkal\TimeHelper;

class ApiController
{
    public function index(Request $request, string $username): Response
    {
        $theme = input('theme', 'dark');
        //获取用户数据
        if (empty($data = Cache::get("user_data_{$username}"))) {
            $json = QueryList::get("https://linux.do/u/{$username}/summary.json")->getHtml();
            $summary = json_decode($json, true);

            $json = QueryList::get("https://linux.do/u/{$username}.json")->getHtml();
            $user = json_decode($json, true);

            //收集数据
            $data = [
                'username'         => $user['user']['username'],
                'trust_level'      => $user['user']['trust_level'],
                'created_at'       => $user['user']['created_at'],
                'last_seen_at'     => $user['user']['last_seen_at'],
                'bio_excerpt'      => $user['user']['bio_excerpt'],
                'days_visited'     => $summary['user_summary']['days_visited'],
                'time_read'        => $summary['user_summary']['time_read'],
                'topics_entered'   => $summary['user_summary']['topics_entered'],
                'posts_read_count' => $summary['user_summary']['posts_read_count'],
                'likes_given'      => $summary['user_summary']['likes_given'],
                'likes_received'   => $summary['user_summary']['likes_received'],
                'post_count'       => $summary['user_summary']['post_count'],
                'solved_count'     => $summary['user_summary']['solved_count'],
            ];

            //缓存数据
            Cache::set("user_data_{$username}", $data, 600);
        }

        //创建图片
        $image = imagecreate(600, 300);
        //设置背景颜色
        $bg_color = imagecolorallocate($image, 34, 34, 34);
        //设置文字颜色
        $color = imagecolorallocate($image, 221, 221, 221);

        if ($theme == 'light') {
            $bg_color = imagecolorallocate($image, 255, 255, 255);
            $color = imagecolorallocate($image, 34, 34, 34);
        }

        //设置字体
        $font = base_path('./public/assets/fonts/bb4171.ttf');
        //填充背景
        imagefill($image, 0, 0, $bg_color);

        //写入基本信息
        imagefttext($image, 18, 0, 30, 50, $color, $font, $data['username'] . ' (' . get_level_name($data['trust_level']) . ')');
        imagefttext($image, 14, 0, 30, 80, $color, $font, mb_strimwidth($data['bio_excerpt'], 0, 32, '...'));
        imagefttext($image, 12, 0, 30, 110, $color, $font, '注册时间');
        imagefttext($image, 12, 0, 330, 110, $color, $font, '最近上线');
        imagefttext($image, 12, 0, 150, 110, $color, $font, TimeHelper::toFriendly(TimeHelper::toTimestamp($data['created_at'])));
        imagefttext($image, 12, 0, 450, 110, $color, $font, TimeHelper::toFriendly(TimeHelper::toTimestamp($data['last_seen_at'])));

        //插入分割线
        imageline($image, 30, 135, 570, 135, $color);

        //写入摘要标题
        imagefttext($image, 16, 0, 30, 180, $color, $font, '访问天数');
        imagefttext($image, 16, 0, 30, 210, $color, $font, '阅读时间');
        imagefttext($image, 16, 0, 30, 240, $color, $font, '浏览话题');
        imagefttext($image, 16, 0, 30, 270, $color, $font, '已读帖子');
        imagefttext($image, 16, 0, 330, 180, $color, $font, '已送出赞');
        imagefttext($image, 16, 0, 330, 210, $color, $font, '已收到赞');
        imagefttext($image, 16, 0, 330, 240, $color, $font, '创建帖子');
        imagefttext($image, 16, 0, 330, 270, $color, $font, '解决方案');

        //写入摘要值
        imagefttext($image, 16, 0, 150, 180, $color, $font, num2friendly($data['days_visited']));
        imagefttext($image, 16, 0, 150, 210, $color, $font, round($data['time_read'] / 3600, 1) . ' 小时');
        imagefttext($image, 16, 0, 150, 240, $color, $font, num2friendly($data['topics_entered']));
        imagefttext($image, 16, 0, 150, 270, $color, $font, num2friendly($data['posts_read_count']));
        imagefttext($image, 16, 0, 450, 180, $color, $font, num2friendly($data['likes_given']));
        imagefttext($image, 16, 0, 450, 210, $color, $font, num2friendly($data['likes_received']));
        imagefttext($image, 16, 0, 450, 240, $color, $font, num2friendly($data['post_count']));
        imagefttext($image, 16, 0, 450, 270, $color, $font, num2friendly($data['solved_count']));

        $image = Image::make($image);
        return response($image->encode('png'), 200, ['Content-Type' => 'image/png']);
    }
}