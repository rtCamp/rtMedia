/* @author: Prabuddha Chakraborty */

module.exports = {

  'Step One : Enable Admin bar menu integration from rtmedia settings ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.othersettings.OTHERSETTINGS)
          .pause(500)

          //Disable Admin bar integration checkbox switch
          .getAttribute(data.selectors.othersettings.SHOW_ADMIN_MENU, "checked", function(result) {
                if(result.value)
                  {
                    browser.click(data.selectors.othersettings.SHOW_ADMIN_MENU);
                    browser.click(data.selectors.SUBMIT);
                  }
                  else
                  {
                    console.log('Admin bar menu integration was already OFF');
                  } })
            .pause(1000)

          },


          'step two: Checking on Frontend ' : function (browser) {
            browser
            .assert.elementNotPresent("#wp-admin-bar-rtMedia > a")
            .wplogout()
            .end();

        }

      };
