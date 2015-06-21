/* @author: Prabuddha Chakraborty */


module.exports = {

  'Step One : Enable Privacy from rtmedia settings ' : function (browser){

    var data = browser.globals;                                                     //fetch variables from constants.js

      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()
          .click(data.selectors.privacy.PRIVACY)                                                 //open privacy tab from rtmedia
          .pause(2000)
          .getAttribute(data.selectors.privacy.ENABLE_PRIVACY, "checked", function(result) {         //select Enable privacy feature

                  if(result.value)
                  {

                      browser.verify.ok(result.value, 'Checkbox is already selected');     //check if privacy is already enabled

                  }
                  else
                  {
                      browser.click(data.selectors.privacy.ENABLE_PRIVACY);                            //Select Enable privacy if already not selected
                      console.log('Privacy is enabled');
                  }
                })

                .getAttribute(data.selectors.privacy.PRIVACY_OVERRIDE, "checked", function(result) {         //select Enable privacy feature

                        if(result.value)
                        {

                            browser.verify.ok(result.value, 'Checkbox is already selected');     //check if privacy is already enabled

                        }
                        else
                        {
                            browser.click(data.selectors.privacy.PRIVACY_OVERRIDE);                            //Select Enable privacy if already not selected
                            console.log('Privacy override is enabled');
                        }
                      })

        .click(data.selectors.privacy.PUBLIC)                                                        //set privacy as public.
        .click(data.selectors.SUBMIT)                                     //Submit to save
        .pause(1000)


        },



  'step two: Upload Media/Post in public privacy ' : function (browser) {
        browser
            .goToActivity()
            .setValue('#whats-new','test privacy for public')
            .click("#rtSelectPrivacy option[value='0']")
            .click('#aw-whats-new-submit')
            .pause(2000)

      /* assert for if post submited in both logged-in & logged-out mode */

            .getText("#activity-stream.activity-list.item-list > li.activity.activity_update.activity-item > div.activity-content > div.activity-inner p", function(result) {
                this.assert.equal(result.value, "test privacy for public");
                browser.wplogout();

                this.assert.equal(result.value, "test privacy for public");
                  })

            .end();

          }


    };
