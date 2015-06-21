/*
 @author: Prabuddha Chakraborty
 TestCase: Check Allow upload from activity stream
 */

module.exports = {
  'Step One : Enable Allow upload from activity stream ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.buddypress.BUDDYPRESS)
          .pause(500)

          //select checkbox switch
          .getAttribute(data.selectors.buddypress.ENABLE_UPLOAD_ACTIVITY, "checked", function(result) {

                  if(result.value){

                    browser.verify.ok(result.value, 'Checkbox is selected');
                    console.log('check box is already enabled');

                  }else{

                    browser.click(data.selectors.buddypress.ENABLE_UPLOAD_ACTIVITY);
                    browser.click(data.selectors.SUBMIT);

                } })
            .pause(1000)

          },


          'step two: Check on Activity Page ' : function (browser) {
            var data = browser.globals;
            browser
            .goToActivity()
            .assert.elementPresent("#rtmedia-add-media-button-post-update")
            .setValue('#rtmedia-whts-new-upload-container input[type="file"]', require('path').resolve(data.path.TEST_IMAGE))
            .setValue('#whats-new','Check  Media Type : Photos (jpg, jpeg, png, gif) ')
            .click('#aw-whats-new-submit')
            .assert.containsText("#buddypress", "test")
            .end();

        }

      };
