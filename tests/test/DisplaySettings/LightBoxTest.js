/*
 @author: Prabuddha Chakraborty
 TestCase: To Check Lightbox Feature
*/

module.exports = {

  'Step One : Enable Allow upload from activity stream ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.display.DISPLAY)
          .pause(1000)

          //select checkbox switch
          .getAttribute(data.selectors.display.ENABLE_LIGHTBOX, "checked", function(result) {

                  if(result.value){

                    browser.verify.ok(result.value, 'Checkbox is selected');
                    console.log('Light box is already on');

                  }else{

                    browser.click(data.selectors.display.ENABLE_LIGHTBOX);
                    browser.click(data.selectors.SUBMIT);

                } })
            .pause(1000)

          },


          'step two: Check on Frontend ' : function (browser) {
            browser
            .goToMedia()
            .click('div.rtmedia-item-thumbnail img')
            .pause(1000)
            .assert.elementPresent('.rtmedia-media')
            .wplogout()
            .end();

        }



        //need to write code for turn off lightbox !!

      };
