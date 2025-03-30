<?php

namespace app\controller;

use app\service\UserService;
use Intervention\Image\ImageManagerStatic as Image;
use support\Request;
use support\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use zjkal\TimeHelper;

class ApiController
{
    public function index(Request $request, string $username): Response
    {
        //获取主题参数
        $theme = input('theme', 'dark');

        //获取用户信息
        $data = UserService::getUserInfo($username);

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
        imagefttext($image, 14, 0, 30, 80, $color, $font, mb_strimwidth($data['bio_excerpt'], 0, 55, '...'));
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

    public function v2(Request $request, string $username): Response
    {
        //获取主题参数
        $theme = input('theme', 'dark');

        //获取用户信息
        $data = UserService::getUserInfo($username);

        //处理基本信息
        $data['bio_excerpt'] = mb_strimwidth($data['bio_excerpt'], 0, 55, '...');
        $data['trust_level'] = get_level_name($data['trust_level']);
        $data['created_at'] = TimeHelper::toFriendly(TimeHelper::toTimestamp($data['created_at']));
        $data['last_seen_at'] = TimeHelper::toFriendly(TimeHelper::toTimestamp($data['last_seen_at']));

        //处理数据
        $data['days_visited'] = num2friendly($data['days_visited']);
        $data['time_read'] = round($data['time_read'] / 3600, 1) . ' 小时';
        $data['topics_entered'] = num2friendly($data['topics_entered']);
        $data['posts_read_count'] = num2friendly($data['posts_read_count']);
        $data['likes_given'] = num2friendly($data['likes_given']);
        $data['likes_received'] = num2friendly($data['likes_received']);
        $data['post_count'] = num2friendly($data['post_count']);
        $data['solved_count'] = num2friendly($data['solved_count']);

        //设置背景颜色
        $bg_color = 'rgba(34, 34, 34, 1)';
        //设置文字颜色
        $color = 'rgba(211, 211, 211, 1)';

        if ($theme == 'light') {
            $bg_color = 'rgba(255, 255, 255, 1)';
            $color = 'rgba(34, 34, 34, 1)';
        } elseif ($theme == 'auto') {
            $bg_color = 'rgba(0, 0, 0, 0)';
            $color = 'rgba(135, 138, 153, 1)';
        }

        $loader = new FilesystemLoader(app_path('view/api'));
        $twig = new Environment($loader, [
            'cache'       => false,
            'auto_reload' => true,
            'debug'       => true
        ]);
        $svg = $twig->render('v2.twig', [
            'bg_color' => $bg_color,
            'color'    => $color,
            'data'     => $data
        ]);
        return response($svg, 200, ['Content-Type' => 'image/svg+xml']);
    }
}