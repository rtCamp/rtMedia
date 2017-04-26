<?php

/**
* Scenario : To set photo thumbnail height and width when crop is disabled.
*/

    use Page\Login as LoginPage;
    use Page\DashboardSettings as DashboardSettingsPage;
    use Page\UploadMedia as UploadMediaPage;
    use Page\Constants as ConstantsPage;

    $I = new AcceptanceTester( $scenario );
    $I->wantTo( 'To set photo thumbnail height and width when crop is disabled.' );

    $loginPage = new LoginPage( $I );
    $loginPage->loginAsAdmin( ConstantsPage::$userName, ConstantsPage::$password );

    $settings = new DashboardSettingsPage( $I );
    $settings->gotoTab( ConstantsPage::$mediaSizesTab, ConstantsPage::$mediaSizesTabUrl );
    $settings->setMediaSize( ConstantsPage::$photoThumbnailLabel, ConstantsPage::$thumbnailWidthTextbox, ConstantsPage::$thumbnailWidth, ConstantsPage::$thumbnailHeightTextbox, ConstantsPage::$thumbnailHeight );

    $I->scrollTo( ConstantsPage::$topSaveButton );

    $settings->verifyDisableStatus( ConstantsPage::$photoThumbnailLabel, ConstantsPage::$thumbnailCropCheckbox );

    $uploadmedia = new UploadMediaPage( $I );
    $uploadmedia->uploadMediaUsingStartUploadButton( ConstantsPage::$userName, ConstantsPage::$imageName, ConstantsPage::$photoLink);

    echo $I->grabAttributeFrom( ConstantsPage::$thumbnailSelector, 'width' );
    echo $I->grabAttributeFrom( ConstantsPage::$thumbnailSelector, 'height' );

?>
