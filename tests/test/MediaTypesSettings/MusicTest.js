/*
 @author: Prabuddha Chakraborty
 TestCase: Music Media Type Test
*/

module.exports = {

  'Step One : Enable Media Types Settings ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()
          .click('#tab-rtmedia-bp')
          .pause(2000)

    /*  'Allow Upload From Activity Stream' is switched  on */

          .getAttribute('#rtmedia-bp-enable-activity', "checked", function(result) {
                if(result.value)
                  {
                    browser.verify.ok(result.value, 'Activity Checkbox is already selected');
                  }
                  else
                  {
                    console.log('enabling activity checkbox')
                    browser.click('#rtmedia-bp-enable-activity');
                    browser.click(data.selectors.SUBMIT);

                } })
          .pause(1000)

          /*  'Enable Music from Types settings */
          .click(data.selectors.mediatypes.MEDIATYPES)

          .getAttribute(data.selectors.mediatypes.ENABLE_MUSIC, "checked", function(result) {
            //  console.log(result); //used for debug
                  if(result.value){
                    browser.verify.ok(result.value, 'Checkbox is selected');
                    console.log('Music check box is already enabled');
                  }
                  else
                  {
                    browser.click(data.selectors.mediatypes.ENABLE_MUSIC);
                    browser.click(data.selectors.SUBMIT);
                } })


          },


          'step two: Check on Frontend with uploading music file' : function (browser) {

            var data = browser.globals;
            browser
            .goToActivity()
            .assert.elementPresent("#rtmedia-add-media-button-post-update")
            .setValue('#rtmedia-whts-new-upload-container input[type="file"]', require('path').resolve(data.path.TEST_MUSIC))
            .setValue('#whats-new','Check Music ')
            .click('#aw-whats-new-submit')


            .assert.containsText("#buddypress", "mpthreetest")


            .wplogout()
            .end();

        }

        };
