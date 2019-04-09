workflow "Deploy" {
  on = "create"
  resolves = ["WordPress Plugin Deploy"]
}

# Filter for tag
action "tag" {
    uses = "actions/bin/filter@master"
    args = "tag"
}

action "WordPress Plugin Deploy" {
  needs = ["tag"]
  uses = "rtCamp/action-wordpress-org-plugin-deploy@master"
  secrets = ["WORDPRESS_USERNAME", "WORDPRESS_PASSWORD"]
  env = {
    SLUG = "buddypress-media"
    ASSETS_DIR = "assets"
    EXCLUDE_LIST = ".bowerrc .gitattributes .gitignore .jshintrc .travis.yml CONTRIBUTING.md Gruntfile.js README.md bin deploy.sh package-lock.json package.json phpunit.xml tests"
  }
}
