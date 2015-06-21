/*
 @author: Prabuddha Chakraborty
 TestCase: Video Media Type Test

*/
module.exports = {

  'Step One : Enable Video Types ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click('#tab-rtmedia-bp')
          .pause(2000)

          /*
          'Allow Upload From Activity Stream' is switched  on
          code here ..

          */
          //select checkbox switch
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
          .click(data.selectors.mediatypes.MEDIATYPES)



          .getAttribute(data.selectors.mediatypes.ENABLE_VIDEO, "checked", function(result) {
            //  console.log(result); //used for debug
                  if(result.value){

                    browser.verify.ok(result.value, 'Checkbox is selected');
                    console.log('Photo check box is already enabled');

                  }else{

                    browser.click(data.selectors.mediatypes.ENABLE_VIDEO);
                    browser.click(data.selectors.SUBMIT);

                } })


          },


          'step two: Check on Frontend ' : function (browser) {
            var data = browser.globals;
            browser
            .goToActivity()


            .assert.elementPresent("#rtmedia-add-media-button-post-update")
            .setValue('#rtmedia-whts-new-upload-container input[type="file"]', require('path').resolve(data.path.TEST_VIDEO))
            .setValue('#whats-new','Check Videos ')
            .click('#aw-whats-new-submit')


            .assert.containsText("#buddypress", "testmpfour")


            .wplogout()
            .end();

        }

        };
