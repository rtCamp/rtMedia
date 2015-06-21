/* @author: Prabuddha Chakraborty */


module.exports = {

  'Step One : Enable Privacy From rtmedia settings ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()
          .click(data.selectors.privacy.PRIVACY)                                                 //open privacy tab from rtmedia
          .pause(2000)
          .getAttribute(data.selectors.privacy.ENABLE_PRIVACY, "checked", function(result) {        //select Enable privacy feature

                  if(result.value)
                  {
                          browser.verify.ok(result.value, 'Checkbox is already selected');

                  }
                  else
                  {
                          browser.click(data.selectors.privacy.ENABLE_PRIVACY);

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


         .click(data.selectors.privacy.LOGGEDIN)                                                    //set privacy as private.
         .click(data.selectors.SUBMIT)            //submit to save

        .pause(1000)

          },


  'step two: Upload  Media/Post for Logged in Users ' : function (browser) {
        browser
            .goToActivity()
            .setValue('#whats-new','Privacy Settings Test For Registered Users')
            .pause(500)
            .click("#rtSelectPrivacy option[value='20']")
            .pause(500)
            .click('#aw-whats-new-submit')
            .pause(2000)
            /* assert for post in  logged-in mode ..
            post should  be availabe on logged-in*/
            .getText("#activity-stream.activity-list.item-list > li.activity.activity_update.activity-item > div.activity-content > div.activity-inner p", function(result) {
                        this.assert.equal(result.value, "Privacy Settings Test For Registered Users");
                      })

            .wplogout()

            /* assert for post in  logged-out mode ..
            post should not be availabe on logged-out*/

            .getText("#activity-stream.activity-list.item-list > li.activity.activity_update.activity-item > div.activity-content > div.activity-inner p", function(result) {

                      this.assert.notEqual(result.value, "Privacy Settings Test For Registered Users");
                        })


            .end();


        }


    };
