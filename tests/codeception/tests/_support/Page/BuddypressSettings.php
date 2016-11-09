<?php
namespace Page;

class BuddypressSettings
{
    // include url of current page
    public static $URL = '';
    public static $userProfileLink = 'a#user-xprofile';
    public static $mediaLinkOnProfile = 'a#user-media';
    public static $myGroupLink = '#groups-personal';
    public static $groupNameLink = 'ul#groups-list li:first-child .item .item-title a';
    public static $mediaLinkOnGroup = 'a#media';
    public static $mediaAlbumLink = 'a#rtmedia-nav-item-albums';


    public static function route($param)
    {
        return static::$URL.$param;
    }

    /**
    * gotoProfilePage() -> Will take the user to his/her profile page
    */
    public static function gotoProfilePage($userName,$I){

        $url = 'members/'.$userName.'/profile';

        $I->amonPage($url);
        $I->seeElement(self::$userProfileLink);

    }

    /**
    * gotoGroupPage() -> Will take the user to group page
    */
    public static function gotoGroupPage($I){

        $I->amonPage('/groups');
        $I->seeElement(self::$myGroupLink);
        $I->click(self::$myGroupLink);
        $I->wait(5);
        $I->seeElement(self::$groupNameLink);
        $I->click(self::$groupNameLink);

    }


}
