module.exports = {
    plugins: [
        require('autoprefixer')({
            overrideBrowserslist: ['last 2 versions', 'ie 9', 'ios 6', 'android 4']
        })
    ]
};
