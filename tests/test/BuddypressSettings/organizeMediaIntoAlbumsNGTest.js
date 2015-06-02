/*
 @author: Prabuddha Chakraborty
 TestCase: To Check Organise Media In album Negative Case
 */


module.exports = {

  'Step One : Enable media in profile  ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.buddypress.BUDDYPRESS)
          .pause(500)

          //select checkbox switch
          .getAttribute(data.selectors.buddypress.ENABLE_MEDIA_ALBUM, "checked", function(result) {
            //  console.log(result); //used for debug
                  if(result.value){

                    browser.click(data.selectors.buddypress.ENABLE_MEDIA_ALBUM);
                    browser.click(data.selectors.SUBMIT);


                  }else{

                    console.log('check box is already enabled');

                } })
            .pause(1000)

          },


          'step two: Check if Album Exist ' : function (browser) {
            browser
            .goToProfile()
            .click('#user-media')
            .assert.elementNotPresent("#rtmedia-nav-item-albums")

           .wplogout()
           .end();


        }




        };
