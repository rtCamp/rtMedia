const { URLS } = require("../utils/urls");

class Activity{
    constructor(page) {
        this.page = page;
    }

    async upploadImages(paths){
        this.gotoActivityPage();
        await this.page.locator("#whats-new").click();
        const [fileChooser] = await Promise.all([
        this.page.waitForEvent('filechooser'),
        this.page.locator('#rtmedia-add-media-button-post-update').click(),
    ]);
    await fileChooser.setFiles(paths);
    }
    async gotoUserProfile(){
        await this.page.goto(URLS.homepage);
        // clicking on the profile Icon
        await this.page.locator("div[class='buddypress-icons-wrapper'] a[class='user-link']").click();
    }

    async gotoActivityPage(){
        await this.page.goto(URLS.homepage + "/activity");
    }
    
    async acceptTermsConsditon(){
        const terms = '#rtmedia_upload_terms_conditions';
        try{
            await this.page.locator('#rtmedia_upload_terms_conditions').click();
        }catch(message){
            console.log('terms not enable');
        }
    }
}
export default Activity;