<?php
namespace Page;

class Login
{

     public static $wpUserNameField = 'input#user_login';
     public static $wpPasswordField = 'input#user_pass';
     public static $wpSubmitButton = 'input#wp-submit';
     public static $currentUrl = 'http://krupa.rtcamp.info/wp-admin/';


    public static function route($param)
    {
        return static::$URL.$param;
    }

    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

    public function login($name, $password)
    {
        $I = $this->tester;

        $I->amOnPage('/');
        $I->fillField(self::$userNameField, $name);
        $I->fillField(self::$passwordField, $password);
        $I->click(self::$loginButton);
        $I->seeInTitle(self::$titleTag);

        return $this;

    }

    public function loginAsAdmin($wpUserName,$wpPassword)
    {
        $I = $this->tester;
        $I->amOnPage('/wp-login.php?redirect_to=http%3A%2F%2Fdemo.rtmedia.io%2Fwp-admin%2F&reauth=1');
        $I->wait(5);
        $I->fillfield(self::$wpUserNameField,$wpUserName);
        $I->fillfield(self::$wpPasswordField,$wpPassword);
        $I->click(self::$wpSubmitButton);
        $I->wait(5);
        $I->see('Dashboard');
        $I->maximizeWindow();
        $I->seeElement('#toplevel_page_rtmedia-settings');

        return $this;
    }

}
