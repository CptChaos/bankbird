<?php

namespace App\Support;

class Demo
{
    public static function active(): bool
    {
        if (app()->runningInConsole()) {
            return false;
        }

        return request()->getHost() === config('app.demo_host', 'demo.bankbird.app');
    }
}
