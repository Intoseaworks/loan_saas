<?php

namespace Common\Utils;

class Until
{
    public static function getCurrentUserModel()
    {
        return \Api\Models\User\User::class;
    }

    public static function isCashnow()
    {
        return env('PROJECT_NAME') == 'CashNow';
    }
}
