/* @author: Prabuddha Chakraborty */

module.exports = {

  'Step One : Enable Admin bar menu integration from rtmedia settings ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.othersettings.OTHERSETTINGS)
          .pause(2000)

          //select checkbox switch
          .getAttribute(data.selectors.othersettings.SHOW_ADMIN_MENU, "checked", function(result) {
              if(result.value)
              {
                    browser.verify.ok(result.value, 'Admin bar menu integration Checkbox is already selected');
              }
              else
              {
                    browser.click(data.selectors.othersettings.SHOW_ADMIN_MENU);
                    browser.click(data.selectors.SUBMIT);
                    console.log('Admin bar menu integration is enabled')
              } })
            .pause(1000)

          },


          'step two: Checking on frontend ' : function (browser) {
            browser
            .assert.elementPresent("#wp-admin-bar-rtMedia > a")
            .wplogout()
            .end();

        }

      };
