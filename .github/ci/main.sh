#!/usr/bin/env bash

set -ex

######################################################
######################## VARS ########################
SITE_NAME='rtmedia.test'
SITE_ROOT="/var/www/$SITE_NAME/htdocs"
SITE_URL="http://$SITE_NAME/"

function ee() { wo "$@"; }
#####################################################

# Start required services for site creation
function start_services() {

    echo "Starting services"
    git config --global user.email "nobody@example.com"
    git config --global user.name "nobody"
    rm /etc/nginx/conf.d/stub_status.conf /etc/nginx/sites-available/22222 /etc/nginx/sites-enabled/22222
    rm -rf /var/www/22222
    ee stack start --nginx --mysql --php74
    ee stack status --nginx --mysql --php74
}

# Remove cache plugins
function remove_cache_plugins () {

    rm -r "$GITHUB_WORKSPACE/plugins/wp-redis"

    rm -r "$GITHUB_WORKSPACE/base/plugins/wp-redis"
}

# Create, setup and populate rtmedia base site
function create_and_configure_base_site () {

    ee site create $SITE_NAME --wp --php74
    cd $SITE_ROOT
    rsync -azh $GITHUB_WORKSPACE/base/ $SITE_ROOT/wp-content/
    echo "127.0.0.1 $SITE_NAME" >> /etc/hosts
    wp user create bob test@example.com --role=administrator --user_pass=password
    wp plugin install buddypress --activate 
    wp plugin install buddypress-media --activate
}


# Install BackstopJS dependency 
function install_wpe2e_package () {

    cd $GITHUB_WORKSPACE/wpe2e
    npm install

}

# Deploy the PR code to site 
function deploy_pr_code_to_site () {

    cd $SITE_ROOT
    rm -r $GITHUB_WORKSPACE/base
    rsync -azh $GITHUB_WORKSPACE/ $SITE_ROOT/wp-content/

}

# Run test for new deployed site
function run_wpe2e_tests () {

    cd $GITHUB_WORKSPACE/wpe2e
    npm run test:e2e -- --wordpress-base-url=http://rtmedia.test/ --wordpress-username=bob --wordpress-password=password

}


function main() {

    start_services
    remove_cache_plugins
    create_and_configure_base_site
    install_wpe2e_package
    create_reference_screenshots
    deploy_pr_code_to_site
    run_wpe2e_tests

}

main
