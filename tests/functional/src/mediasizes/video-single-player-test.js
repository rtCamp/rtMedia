/*
 @author: Prabuddha Chakraborty
 TestCase: Video Single Player Test
*/
var _width = 300;       //set width here
var _height = 250 ;     //set height here
module.exports = {
  tags: ['mediasize', 'video','upload'],
  'Step One : Set single Video Width and Height ' : function (browser){
  var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.URLS.LOGIN,data.TESTADMINUSERNAME,data.TESTADMINPASSWORD)
          .openrtMediaSettings()
          .click(data.SELECTORS.MEDIASIZES.MEDIASIZES)
          .pause(2000)
          .clearValue(data.SELECTORS.MEDIASIZES.VIDEO_SINGLE_PLAYER_WIDTH)
          .setValue(data.SELECTORS.MEDIASIZES.VIDEO_SINGLE_PLAYER_WIDTH,_width) //set width size:200
          .clearValue(data.SELECTORS.MEDIASIZES.VIDEO_SINGLE_PLAYER_HEIGHT)
          .setValue(data.SELECTORS.MEDIASIZES.VIDEO_SINGLE_PLAYER_HEIGHT,_height) //set height size:200
          .click(data.SELECTORS.SUBMIT)
          .pause(1000)
  },
      'step two: Check Video Size on Frontend ' : function (browser) {
      var data = browser.globals;
        browser
          .goToMedia()
          .click('#rtmedia-nav-item-video')
          .waitForElementVisible('body', 1500)
          .pause(1000)
          .click('#rtm_show_upload_ui')
          .waitForElementVisible('.rtm-select-files',1500)
          .click('.rtm-select-files')
          .setValue('input[type=file]', require('path').resolve(data.PATH.TEST_VIDEO))
          .click('.start-media-upload')
          .pause(8000)
          .refresh()
          .click('.rtmedia-item-thumbnail img')
          .waitForElementVisible('body', 1500)
          .waitForElementVisible('#rtm-mejs-video-container',1500)
          .getElementSize("#rtm-mejs-video-container", function(result) {
                  this.assert.equal(result.value.width, _width,'Entered value for width is equal');
                  this.assert.equal(result.value.height, _height,'Entered value for height is equal');
                  // console.log('set value for width are equal');
              })
          .wplogout()
          .end();
                  }
};
