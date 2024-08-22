<?php
/**
 * Here is your custom functions.
 */
function num2friendly($number)
{
    if ($number >= 10000 and $number < 100000000) {
        return round($number / 10000, 2) . '万';
    } elseif ($number >= 100000000) {
        return round($number / 100000000, 2) . '亿';
    }
    return $number;
}

function get_level_name($level): string
{
    switch ($level) {
        case 0:
            return '空气';
        case 1:
            return '一级新萌';
        case 2:
            return '二级老萌';
        case 3:
            return '三级大佬';
        case 4:
            return '四级大神';
        case 5:
            return '五级超神';
        default:
            return '游客';
    }
}