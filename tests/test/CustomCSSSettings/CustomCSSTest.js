/*
 @author: Prabuddha Chakraborty
 TestCase: To Check Custom CSS Settings
*/

module.exports = {

  'Step: Add custom css in rtmedia settings and Verify on FRONTEND' : function (browser){
    var data = browser.globals;
      browser
          .maximizeWindow()
          .wplogin(data.urls.LOGIN,data.USERNAME,data.PASSWORD)
          .openrtMediaSettings()

          .click(data.selectors.customcss.CUSTOM_CSS)
          .getAttribute(data.selectors.customcss.DEFAULT_ENABLE, "checked", function(result) {
                if(result.value)
                  {
                    browser.click(data.selectors.customcss.DEFAULT_ENABLE);
                    console.log('CUSTOM CSS Checkbox is disabled');
                  }
                  else
                  {
                    console.log('CUSTOM CSS is already disabled');

                } })
          .pause(1000)


          .click(data.selectors.customcss.CUSTOM_CSS_TEXTAREA)
          .clearValue(data.selectors.customcss.CUSTOM_CSS_TEXTAREA)
          .setValue(data.selectors.customcss.CUSTOM_CSS_TEXTAREA,"#buddypress #whats-new { height: 500px !important; overflow: hidden;")
          .pause(200)
          .click(data.selectors.SUBMIT)


          /* move to Activity page ..and verify changes */
          .moveToElement('#wp-admin-bar-my-account > a.ab-item',5,5)
          .pause(500)
          .click('#wp-admin-bar-my-account-activity a.ab-item')

          .getElementSize("#buddypress #whats-new", function(result) {

                  this.assert.equal(result.value.height, 500);
                  console.log(result.value.height);

          })



          /*Restore to the old settings */


          .openrtMediaSettings()
          .click(data.selectors.customcss.CUSTOM_CSS)
          .click(data.selectors.customcss.DEFAULT_ENABLE)
          .clearValue(data.selectors.customcss.CUSTOM_CSS_TEXTAREA)
          .click(data.selectors.SUBMIT,function(){
                                        console.log("Restored to the old settings");
                                              })


          .wplogout()
          .end();


  }




};
