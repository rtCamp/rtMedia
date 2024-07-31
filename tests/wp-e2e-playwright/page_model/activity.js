const { URLS } = require("../utils/urls");

class Activity{
    constructor(page) {
        this.page = page;
    }

    async upploadMedia(paths){
        this.gotoActivityPage();
        await this.page.locator("#whats-new").click();
        const [fileChooser] = await Promise.all([
        this.page.waitForEvent('filechooser'),
        this.page.locator('#rtmedia-add-media-button-post-update').click(),
    ]);

    await fileChooser.setFiles(paths);
    await this.page.waitForLoadState('domcontentloaded');
    await this.page.waitForLoadState('networkidle')
    }
    async gotoUserProfile(){
        await this.gotoActivityPage();
        await this.page.locator("#whats-new-avatar").click();
    }

    async gotoActivityPage(){
        await this.page.goto(URLS.homepage + "/activity");
    }

    async getPhotoSize(){
        const imgLocator = this.page.locator('div.rtmedia-item-thumbnail img').first();;
        const srcValue = await imgLocator.getAttribute('src');
        return srcValue;
    }
    
    async clickedOnFirstPhotoOfTheActivityPage(){
        this.gotoActivityPage();
        await this.page.waitForLoadState("domcontentloaded");
        await this.page.locator("//div[@class='rtmedia-item-thumbnail']").first().click();
    }
    async acceptTermsConsditon(){
        try{
            await this.page.locator('#rtmedia_upload_terms_conditions').click();
        }catch(message){
            console.log('terms not enable');
        }
    }
}
export default Activity;