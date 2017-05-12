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
    //   $I->wait( 3 );
        $I->waitForElement( ConstantsPage::$manageGrpLink, 10 );

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
        // $I->wait( 5 );
        $I->waitForElement( ConstantsPage::$nextGrpButton, 20);

        self::gotoGroup();

    }

    /**
    * gotoActivityPage() -> Will take the user to activity page
    */
    public function gotoActivityPage( $userName ){

        $I = $this->tester;

        $url = 'members/'.$userName;
        $I->amOnPage($url);
        // $I->wait( 5 );
        $I->waitForElement( ConstantsPage::$mediaPageScrollPos, 10);
        // $I->seeElement( ConstantsPage::$mediaPageScrollPos );
        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );
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

        $albumName = 'My test album';
        $albumCreationMsg = $albumName.ConstantsPage::$albumMsg;

        $I = $this->tester;

        self::gotoAlubmPage();

        $I->scrollTo( ConstantsPage::$mediaPageScrollPos );

        $I->seeElement( ConstantsPage::$mediaOptionButton );
        $I->click( ConstantsPage::$mediaOptionButton );
        // $I->wait( 2 );
        $I->waitForElementVisible( ConstantsPage::$optionsPopup, 10 );

        $I->seeElement( ConstantsPage::$addAlbumButtonLink );
        // $I->waitForElement('selector', 60);
        $I->click( ConstantsPage::$addAlbumButtonLink );

        $I->waitForElementVisible( ConstantsPage::$createAlbumPopup, 10 );
        // $I->seeElement( ConstantsPage::$createAlbumPopup );
        $I->seeElement( ConstantsPage::$albumNameTextbox );
        $I->fillField( ConstantsPage::$albumNameTextbox, $albumName );
        $I->seeElement( ConstantsPage::$createAlbumButton );
        $I->click( ConstantsPage::$createAlbumButton );
        // $I->wait( 5 );
        $I->waitForText( $albumCreationMsg, 20 );

        $I->seeElement( ConstantsPage::$closeAlbumButton );
        $I->click( ConstantsPage::$closeAlbumButton );
        echo "Album created";

        // $I->wait( 5 );
        $I->reloadPage();
        $I->waitForElement( ConstantsPage::$profilePicture, 10);

    }

    /**
    * editAlbumDesc() -> Will edit the desc for created new album
    */
    public function editAlbumDesc(){

        $albumDesc = 'My test album desc';
        $I = $this->tester;
        echo "Inside edit album function";

        $I->seeElement( ConstantsPage::$firstAlbum );
        $I->click( ConstantsPage::$firstAlbum );
        // $I->wait( 5 );
        // $I->waitForElement( ConstantsPage::$profilePicture, 10);

        $I->waitForElementVisible( ConstantsPage::$scrollSelector, 20 );
        $I->scrollTo( ConstantsPage::$scrollSelector );

        $I->seeElement( ConstantsPage::$mediaOptionButton );
        $I->click( ConstantsPage::$mediaOptionButton );
        // $I->wait( 2 );
        $I->waitForElementVisible( ConstantsPage::$optionsPopup, 10 );

        $I->seeElement( ConstantsPage::$albumEditLink );
        $I->click( ConstantsPage::$albumEditLink );
        // $I->wait( 5 );
        // $I->waitForElementVisible( ConstantsPage::$profilePicture, 20 );

        // $I->seeElement( ConstantsPage::$scrollSelector );
        // $I->scrollTo( ConstantsPage::$scrollSelector );
        $I->waitForElementVisible( ConstantsPage::$scrollSelector, 20 );
        $I->scrollTo( ConstantsPage::$scrollSelector );

        $I->seeElement( ConstantsPage::$albumDescTeaxtarea );
        $I->fillField( ConstantsPage::$albumDescTeaxtarea, $albumDesc );
        $I->seeElement( ConstantsPage::$saveAlbumButton );
        $I->click( ConstantsPage::$saveAlbumButton );
        // $I->wait( 5 );
        $I->waitForElementVisible( ConstantsPage::$profilePicture, 20);
    }

    // public function backToAlbumPage(){
    //
    //     $I = $this->tester;
    //
    //     $I->seeElementInDOM( ConstantsPage::$scrollSelector );
    //     $I->scrollTo( ConstantsPage::$scrollSelector );
    //
    //     $I->seeElement( ConstantsPage::$goBackToAlbumPage );
    //     $I->click( ConstantsPage::$goBackToAlbumPage );
    //     // $I->wait( 5 );
    //     $I->waitForElement( ConstantsPage::$profilePicture, 20);
    //
    // }

}
