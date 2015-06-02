/*
 @author: Prabuddha Chakraborty
 TestCase: Music Single Player Test
*/


module.exports = {

  'Step One : Set width single player width  ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.mediasizes.MEDIASIZES)
          .pause(2000)
          .clearValue(data.selectors.mediasizes.MUSIC_SINGLEPLAYER_WIDTH)
          .setValue(data.selectors.mediasizes.MUSIC_SINGLEPLAYER_WIDTH,'200') //set size:200
          .click(data.selectors.SUBMIT)
          .pause(1000)

          //disable lightbox
          .click(data.selectors.display.DISPLAY)


          //disable lightbox checkbox switch
          .getAttribute(data.selectors.display.ENABLE_LIGHTBOX, "checked", function(result) {
            //  console.log(result); //used for debug
                  if(result.value){
                    browser.click(data.selectors.display.ENABLE_LIGHTBOX);
                    browser.click(data.selectors.SUBMIT);


                  }else{

                    console.log('Light box is already disabled');


                } })
            .pause(1000)


          },


                    'step two: Check on Frontend ' : function (browser) {
                      var data = browser.globals;
                      browser
                      .goToMedia()
                      .click('#rtmedia-nav-item-music')
                      .waitForElementVisible('body', 1500)
                      .click('#rtm_show_upload_ui')
                      .click('.rtm-select-files')
                    .setValue('input[type=file]', require('path').resolve(data.path.TEST_MUSIC))
                          .click('.start-media-upload')
                        .pause(8000)
                        .refresh()

                      .click('.rtmedia-item-thumbnail')

                      .getElementSize(".mejs-container", function(result) {  //#mep_0

                            this.assert.equal(result.value.width, 200);
                            console.log('set value for width are equal');


                  })




                      .wplogout()
                      .end();

                  }

};
