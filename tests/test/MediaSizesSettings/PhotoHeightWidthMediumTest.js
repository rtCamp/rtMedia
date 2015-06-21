/*
 @author: Prabuddha Chakraborty
 TestCase: Photo Dimension Test
*/

module.exports = {

  'Step One :  ' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.mediasizes.MEDIASIZES)
          .pause(2000)
          .clearValue(data.selectors.mediasizes.PHOTO_MEDIUM_WIDTH)
          .setValue(data.selectors.mediasizes.PHOTO_MEDIUM_WIDTH,'100') //set width size:100
          .clearValue(data.selectors.mediasizes.PHOTO_MEDIUM_HEIGHT)
          .setValue(data.selectors.mediasizes.PHOTO_MEDIUM_HEIGHT,'100') //set height size:100
          .click(data.selectors.SUBMIT)
          .pause(1000)



          },


                    'step two: Check on Frontend ' : function (browser) {

                  var data = browser.globals;
                      browser
                    .goToActivity()


                      .assert.elementPresent("#rtmedia-add-media-button-post-update")
                      .setValue('#rtmedia-whts-new-upload-container input[type="file"]', require('path').resolve(data.path.TEST_IMAGE))
                      .setValue('#whats-new','testing for image size in activity')
                      .click('#aw-whats-new-submit')
                      .refresh()
                      .pause(2000)



                      .getElementSize(".rtmedia-item-thumbnail img", function(result) {

                            this.assert.equal(result.value.height, 100)
                            this.assert.equal(result.value.width, 100);


                  })




                      .wplogout()
                      .end();

                  }

        };
