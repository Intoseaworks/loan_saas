<?php


namespace Common\Utils\Push\Services;


interface PushInterface
{
    public function sendMessage($deviceToken, $title, $message, $custom = []);
}
