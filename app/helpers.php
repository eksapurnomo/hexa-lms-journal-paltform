<?php

use App\Enum\NotificationTypeEnum;
use App\Repositories\SettingRepository;

if (!function_exists('currency')) {
    function currency($amount)
    {
        $setting = SettingRepository::query()->get()->first();
        $currencySymbol = config('app.currency_symbol');
        $currencyPosition = $setting?->currency_position ?? "Left";
        $finalPosition = number_format($amount, 2) . $currencySymbol;

        if ($currencyPosition == 'Left') {
            $finalPosition = $currencySymbol . number_format($amount, 2);
        }

        return $finalPosition;
    }
}

if (!function_exists('filterPermission')) {
    function filterPermission($name)
    {
        if ($name == "index") {
            return "list";
        }
        if ($name == "destroy") {
            return "delete";
        }
        if ($name == "free") {
            return "course free";
        }

        if ($name == "select_course") {
            return "show course list";
        }

        if ($name == "assign_roletopermission") {
            return "assign role to permission";
        }

        if ($name == "get_permission") {
            return "get permission";
        }
        return $name;
    }
}


if (!function_exists('filterNotificationType')) {

    function filterNotificationType($type)
    {

        $getSpaceBetween = str_replace('_', ' ', $type?->value);
        $filterType = ucWords(strtolower($getSpaceBetween));
        return  $filterType;
    }
}
