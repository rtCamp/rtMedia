<?php
namespace Page;

class BuddypressSettings
{
    public static $userProfileLink = 'a#user-xprofile';
    public static $mediaLinkOnProfile = 'a#user-media';
    public static $myGroupLink = '#groups-personal';
    public static $groupNameLink = 'ul#groups-list li:first-child .item .item-title a';

    /**
    * gotoProfilePage() -> Will take the user to his/her profile page
    */
    public function gotoProfile($I,$userName){

        $url = 'members/'.$userName.'/profile';
        $I->amOnPage($url);

    }

    /**
    * gotoGroupPage() -> Will take the user to group page
    */
    public function gotoGroup($I){

        $I->amonPage('/groups');
        $I->seeElement(self::$myGroupLink);
        $I->click(self::$myGroupLink);
        $I->wait(5);
        $I->seeElement(self::$groupNameLink);
        $I->click(self::$groupNameLink);
        $I->wait(3);

    }
}
