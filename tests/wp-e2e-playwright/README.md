# wp-e2e
This is a generic automation test suit using WP Gutenberg Playwright Utils 
Used Framewrok
1. Playwright https://playwright.dev/
2. WordPress E2E Playwright Utils https://github.com/WordPress/gutenberg/tree/trunk/packages/e2e-test-utils-playwright

## Install
`npm install`

`npm run build`



## Run all available tests.
`npm run test-e2e:playwright`

## Run in headed mode.`
`npm run test-e2e:playwright -- --headed`

## Run a single test file.
`npm run test-e2e:playwright -- <path_to_test_file> # E.g., npm run test-e2e:playwright -- add-new-post.spec.js`

## Debugging
`npm run test-e2e:playwright -- --debug`

## Migration
We can migrate wp-e2e generic test cases from [here](https://github.com/rtCamp/wp-e2e/tree/master/specs) using the steps mentioned in [this](https://github.com/WordPress/gutenberg/pull/38570)



