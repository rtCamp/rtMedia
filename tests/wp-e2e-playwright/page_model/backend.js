class Backend {
    constructor(page) {
        this.page = page;
    }

    async enableAnySettingAndSave(selector) {
        await this.page.locator(selector).check();
        await this.page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
    }
    async disableAnySettingAndSave(selector) {
        await this.page.locator(selector).uncheck();
        await this.page.locator("div[class='rtm-button-container bottom'] input[value='Save Settings']").click();
    }
}

export default Backend;
