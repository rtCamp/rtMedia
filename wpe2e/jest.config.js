const config = require( '@wordpress/scripts/config/jest-e2e.config.js' );

const jestE2EConfig = {
	...config,
	setupFilesAfterEnv: [
		'<rootDir>/config/bootstrap.js',
	],
};

module.exports = jestE2EConfig;
