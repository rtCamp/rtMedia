<?php
namespace Page;

use Page\Constants as ConstantsPage;

class BuddypressSettings
{
    public static $userProfileLink = 'a#user-xprofile';
    public static $mediaLinkOnProfile = 'a#user-media';
    public static $myGroupLink = '#groups-personal';
    public static $groupNameLink = 'ul#groups-list li:first-child .item .item-title a';

    protected $tester;

    public function __construct( \AcceptanceTester $I )
    {
        $this->tester = $I;
    }

    /**
    * gotoProfilePage() -> Will take the user to his/her profile page
    */
    public function gotoProfile($I,$userName){

        $url = 'members/'.$userName.'/profile';
        $I->amOnPage($url);
        $I->wait(5);

    }

    /**
    * countGroup() -> Will count the no of groups available
    */
    public function countGroup( $selector ){

        $I = $this->tester;

        $groupsArray = $I->grabMultiple( $selector );

        return count( $groupsArray );

    }

    /**
    * checkMediaInGroup() -> Will check if the media is available in group
    */
    public function checkMediaInGroup(){

      $I = $this->tester;

      $I->seeElement( self::$groupNameLink );
      $I->click( self::$groupNameLink );
      $I->wait( 3 );

    }

    /**
    * gotoGroupPage() -> Will take the user to group page
    */
    public function gotoGroup(){

        $I = $this->tester;

        $I->amonPage('/groups');
        $I->wait(5);

    }

    /**
    * createGroup() -> Will create a new group
    */
    public function createGroup(){
        echo "this is from create grp function.";

        $I = $this->tester;

        $I->seeElementInDOM( ConstantsPage::$createGroupLink );
        $I->click( ConstantsPage::$createGroupLink );

        $I->wait( 10 );

        $I->seeElementInDOM( ConstantsPage::$createGroupTabs );

        $I->scrollTo( ConstantsPage::$createGroupTabs );

        $I->seeElementInDOM( ConstantsPage::$groupNameTextbox );
        $I->fillField( ConstantsPage::$groupNameTextbox, 'Test Group Name from Script' );

        $I->seeElementInDOM( ConstantsPage::$groupDescTextarea );
        $I->fillField( ConstantsPage::$groupDescTextarea, 'Test Group Desc from Script' );  // Enter group Description

        $I->seeElement( ConstantsPage::$createGroupButton );
        $I->click( ConstantsPage::$createGroupButton );
        $I->wait( 5 );

        self::gotoGroup();

    }

    /**
    * gotoActivityPage() -> Will take the user to activity page
    */
    public function gotoActivityPage($I,$userName){

        $url = 'members/'.$userName;
        $I->amOnPage($url);
        $I->wait(5);

    }

    public function gotoPhotoPage( $userName ){

        $I = $this->tester;

        $url = 'members/'.$userName.'/media/photo';
        $I->amOnPage($url);

        $I->wait(10);

    }
}
