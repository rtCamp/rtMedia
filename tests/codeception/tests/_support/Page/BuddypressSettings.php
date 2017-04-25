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
    public function gotoProfile( $userName ){

        $I = $this->tester;

        $url = 'members/'.$userName.'/profile';
        $I->amOnPage( $url );
        $I->waitForElement( ConstantsPage::$profilePicture, 5 );

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
        $I->waitForElement( ConstantsPage::$createGroupLink, 5 );

    }

    /**
    * createGroup() -> Will create a new group
    */
    public function createGroup(){

        echo "this is from create grp function.";

        $I = $this->tester;

        $I->seeElementInDOM( ConstantsPage::$createGroupLink );
        $I->click( ConstantsPage::$createGroupLink );

        $I->waitForElement( ConstantsPage::$createGroupTabs, 5 );
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
    public function gotoActivityPage( $userName ){

        $I = $this->tester;

        $url = 'members/'.$userName;
        $I->amOnPage($url);
        $I->waitForElement('#whats-new-textarea', 10 );

    }

    /**
    * gotoMedia() -> Will take the user to media page
    */
    public function gotoMedia( $userName ){

        $I = $this->tester;

        $url = 'members/'.$userName.'/media';
        $I->amOnPage($url);

        $I->waitForElement( ConstantsPage::$profilePicture, 5 );

    }

    /**
    * gotoPhotoPage() -> Will take the user to photo page
    */
    public function gotoPhotoPage( $userName ){

        $I = $this->tester;

        $url = 'members/'.$userName.'/media/photo';
        $I->amOnPage( $url );
        $I->waitForElement('div.rtmedia-container', 10);

    }

    /**
    * countMedia() -> Will count media
    */
    public function countMedia( $selector ){

        $I = $this->tester;

        $mediaArray = $I->grabMultiple( $selector ); // This will grab the no. of media available on media page
        echo nl2br( 'No of media on page = '. count( $mediaArray ) );

        return count( $mediaArray );

    }

    /**
    * gotoAlubmPage() -> Will take the user to album page
    */
    public function gotoAlubmPage(){

        $I = $this->tester;

        $url = 'members/'.ConstantsPage::$userName.'/media/album/';
        $I->amOnPage( $url );
        $I->waitForElement('div.rtmedia-container', 10);
    }

    /**
    * createNewAlbum() -> Will create new album
    */
    public function createNewAlbum(){

        $I = $this->tester;

        self::gotoAlubmPage();

        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $I->seeElement( ConstantsPage::$mediaOptionButton );
        $I->click( ConstantsPage::$mediaOptionButton );
        $I->wait( 2 );

        $I->seeElement( ConstantsPage::$addAlbumButtonLink );
        $I->click( ConstantsPage::$addAlbumButtonLink );

        $I->seeElement( ConstantsPage::$createAlbumPopup );
        $I->seeElement( ConstantsPage::$albumNameTextbox );
        $I->fillField( ConstantsPage::$albumNameTextbox, 'My test album - 1');
        $I->seeElement( ConstantsPage::$createAlbumButton );
        $I->click( ConstantsPage::$createAlbumButton );
        $I->wait( 5 );

        $I->seeElement( ConstantsPage::$closeAlbumButton );
        $I->click( ConstantsPage::$closeAlbumButton );

        $I->wait( 5 );

        echo "Album created";

    }

    /**
    * editAlbumDesc() -> Will edit the desc for created new album
    */
    public function editAlbumDesc(){

        $I = $this->tester;

        // $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $I->seeElement( ConstantsPage::$firstAlbum );
        $I->click( ConstantsPage::$firstAlbum );
        // $I->waitForElementVisible( ConstantsPage::$mediaPageScrollPos, 5);

        // $I->scrollTo( ConstantsPage::$mediaPageScrollPos );
        $I->wait( 5 );
        echo "On newly created media page";
        $I->seeElement( 'a#rtmedia-nav-item-albums' );
        $I->scrollTo( 'a#rtmedia-nav-item-albums' );
        echo "After Scroll to media tab";

        $I->seeElement( ConstantsPage::$mediaOptionButton );
        $I->click( ConstantsPage::$mediaOptionButton );
        $I->wait( 2 );

        $I->seeElement( ConstantsPage::$albumEditLink );
        $I->click( ConstantsPage::$albumEditLink );

        $I->wait( 5 );
        echo "On newly created media edit page";
        $I->seeElement( 'a#rtmedia-nav-item-albums' );
        $I->scrollTo( 'a#rtmedia-nav-item-albums' );
        echo "After Scroll to media tab";

        // $I->waitForElementVisible( ConstantsPage::$mediaPageScrollPos, 5);
        //
        // $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $I->seeElement( ConstantsPage::$albumDescTeaxtarea );
        $I->fillField( ConstantsPage::$albumDescTeaxtarea, 'My test album - desc - 1');
        $I->seeElement( ConstantsPage::$saveAlbumButton );
        $I->click( ConstantsPage::$saveAlbumButton );

        $I->wait( 5 );
        echo "After album desc added!";
    }
}
