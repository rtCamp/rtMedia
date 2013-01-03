#! /bin/bash
# A modification of Dean Clatworthy's deploy script as found here: https://github.com/deanc/wordpress-plugin-git-svn
# The difference is that this script lives in the plugin's git repo & doesn't require an existing SVN repo.

# main config
export PLUGINSLUG="buddypress-media"  #must match with wordpress.org plugin slug
export MAINFILE="index.php" # this should be the name of your main php file in the wordpress plugin
#SVNUSER="rtcamp" # your svn username
/bin/bash deploy-common.sh

