/*
 @author: Prabuddha Chakraborty
 TestCase: Photo Dimension Test
*/

module.exports = {

  'Step One : Set Dimensions of Photo [Large]  ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.mediasizes.MEDIASIZES)
          .pause(2000)
          .clearValue(data.selectors.mediasizes.PHOTO_LARGE_WIDTH)
          .setValue(data.selectors.mediasizes.PHOTO_LARGE_WIDTH,'300') //set width size:300
          .clearValue(data.selectors.mediasizes.PHOTO_LARGE_HEIGHT)
          .setValue(data.selectors.mediasizes.PHOTO_LARGE_HEIGHT,'300') //set height size:300
          .click(data.selectors.SUBMIT)
          .pause(1000)

          //disable lightbox
          .click(data.selectors.display.DISPLAY)


          //select checkbox switch
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
                      .click('#rtmedia-nav-item-photo')
                      .pause(1000)

                      //upload

                      .waitForElementVisible('body', 1500)

                      .click('#rtm_show_upload_ui')
                      .click('.rtm-select-files')
                      .setValue('input[type=file]', require('path').resolve(data.path.TEST_IMAGE))
                      .click('.start-media-upload')
                      .pause(8000)
                      .refresh()
                      .pause(1000)
                      .click('.rtmedia-list-item .rtmedia-list-item-a .rtmedia-item-thumbnail img')
                      .waitForElementVisible('body', 1500)

                      .getElementSize('.rtmedia-media img', function(result) {

                            this.assert.equal(result.value.height, 300)
                            this.assert.equal(result.value.width, 300);


                  })




                      .wplogout()
                      .end();

                  }

        };
