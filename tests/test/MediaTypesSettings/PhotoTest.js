/*
 @author: Prabuddha Chakraborty
 TestCase: Photo Media Type Test
*/
module.exports = {

  'Step One : Enable Photo Types From Settings ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click('#tab-rtmedia-bp')
          .pause(2000)

          //select checkbox switch
          .getAttribute("#rtmedia-bp-enable-activity", "checked", function(result) {
            //  console.log(result); //used for debug
                  if(result.value){

                    browser.verify.ok(result.value, 'Checkbox is selected');
                    console.log('check box is already enabled');

                  }else{

                    browser.click('#rtmedia-bp-enable-activity');
                    browser.click(data.selectors.SUBMIT);

                } })

          /*


          'Allow Upload From Activity Stream' is switched  on
          code here ..

          */

          .pause(1000)
          .click(data.selectors.mediatypes.MEDIATYPES)



          .getAttribute(data.selectors.mediatypes.ENABLE_PHOTO, "checked", function(result) {
            //  console.log(result); //used for debug
                  if(result.value){

                    browser.verify.ok(result.value, 'Checkbox is selected');
                    console.log('Photo check box is already enabled');

                  }else{

                    browser.click(data.selectors.mediatypes.ENABLE_PHOTO);
                    browser.click(data.selectors.SUBMIT);

                } })


          },


          'step two: Check on Frontend ' : function (browser) {
            var data = browser.globals;
            browser
            .goToActivity()


            .assert.elementPresent("#rtmedia-add-media-button-post-update")
            .setValue('#rtmedia-whts-new-upload-container input[type="file"]', require('path').resolve(data.path.TEST_IMAGE))
            .setValue('#whats-new','Check  Media Type : Photos (jpg, jpeg, png, gif) ')
            .click('#aw-whats-new-submit')


            .assert.containsText("#buddypress", "test")


            .wplogout()
            .end();

        }

        };
