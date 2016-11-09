<?php
namespace Page;

class PrivacySettings
{
    // include url of current page
    public static $URL = '';
    public static $privacyDropdown = '#rtSelectPrivacy';

    public static function route($param)
    {
        return static::$URL.$param;
    }


}
