<?php
namespace Page;

use Page\Constants as ConstantsPage;

class Setup
{

    protected $tester;
    public function __construct( \AcceptanceTester $I )
    {
        $this->tester = $I;
    }

    public static function route($param)
    {
        return static::$URL.$param;
    }

    public function activatePlugin(){

        $I = $this->tester;

        $I->scrollTo( ConstantsPage::$scrollPosToForPlugin );
        $I->seeElementInDOM( ConstantsPage::$activateLink );
        $I->click( ConstantsPage::$activateLink );
        $I->wait( 5 );

    }

    public function deactivatePlugin(){

        $I = $this->tester;

        $I->scrollTo( ConstantsPage::$scrollPosToForPlugin );
        $I->seeElementInDOM( ConstantsPage::$deactivateLink );
        $I->click( ConstantsPage::$deactivateLink );
        $I->wait( 5 );

    }

    public function uploadAndInstallPlugin(){

        $I = $this->tester;

        $I->see('Add New');
        $I->seeElement( ConstantsPage::$addNewPluginButton );
        $I->click( ConstantsPage::$addNewPluginButton );
        $I->wait( 10 );

        $I->seeInCurrentUrl( ConstantsPage::$gotoPluginInstallPage );

        $I->see( 'Upload Plugin' );
        $I->seeElementInDOM( ConstantsPage::$uploadPluginButton );
        $I->click( ConstantsPage::$uploadPluginButton );
        $I->wait( 3 );

        $I->seeElementInDOM( ConstantsPage::$choosefileButton );
        $I->attachFile( ConstantsPage::$choosefileButton ,'rtmedia.zip');
        $I->wait( 10 );

        $I->seeElementInDOM( ConstantsPage::$installPluginButton );
        $I->click( ConstantsPage::$installPluginButton );
        $I->wait( 5 );
        $I->seeInCurrentUrl( ConstantsPage::$uploadPluginPage );

    }

    public function searchAndInstallPlugin(){

        $I = $this->tester;

        $I->see( 'Add New' );
        $I->seeElement( ConstantsPage::$addNewPluginButton );
        $I->click( ConstantsPage::$addNewPluginButton );
        $I->waitForElementVisible( ConstantsPage::$searchPluginTextbox, 10);

        $I->seeInCurrentUrl( ConstantsPage::$gotoPluginInstallPage );
        $I->fillField( ConstantsPage::$searchPluginTextbox,'rtmedia' );
        $I->waitForText('rtMedia for WordPress, BuddyPress and bbPress', 10);

        $I->seeElementInDOM( ConstantsPage::$installNowButton );
        $I->click( ConstantsPage::$installNowButton );
        $I->wait( 10 );

        $I->waitForElement( ConstantsPage::$activatePluginButton, 10);
        $I->click( ConstantsPage::$activatePluginButton );
        $I->wait( 10 );

        $I->amOnPage( ConstantsPage::$gotoActivePluginPage );
        $I->wait( 5 );
        $I->see( 'rtMedia for WordPress, BuddyPress and bbPress' );

    }

    public function removePlugin(){

        $I = $this->tester;

        self::deactivatePlugin();

        $I->amOnPage( ConstantsPage::$gotoInactivePluginPage );
        $I->wait( 5 );

        $I->scrollTo( ConstantsPage::$pluginDataslug );
        $I->seeElementInDOM( ConstantsPage::$deletePluginLink );
        $I->click( ConstantsPage::$deletePluginLink );
        $I->wait( 3 );

        $I->acceptPopup();
        $I->wait( 5 );

        $I->amOnPage( ConstantsPage::$allPluginsUrl );
        $I->dontSeeInSource('<strong>rtMedia for WordPress, BuddyPress and bbPress</strong>');

    }

}
