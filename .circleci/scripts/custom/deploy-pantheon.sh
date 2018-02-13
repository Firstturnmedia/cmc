#!/bin/bash

# Make sure feature branch names work with Pantheon's 11 char max.

# Multidev
if [ "$CIRCLE_BRANCH" != "master" ]; then
  # Log in w/ terminus
  terminus -n auth:login --machine-token="$TERMINUS_TOKEN"

  # Check if multidev aleeady exists. If not, create, else, update existing
  if ! terminus multidev:list $TERMINUS_SITE --field id | grep $CIRCLE_BRANCH; then
    echo "Creating multidev $CIRCLE_BRANCH-$TERMINUS_SITE.dev"
    terminus env:wake "$TERMINUS_SITE.dev"
    terminus multidev:create $TERMINUS_SITE.dev $CIRCLE_BRANCH
    else
      echo "Existing multidev $CIRCLE_BRANCH-$TERMINUS_SITE will be updated."
  fi
  # Show the present working directory
  pwd

  # Show the latest commit msg
  echo "Show the latest commit message"
  git log --format=oneline -n 1 $CIRCLE_SHA1

  # Remove any existing build
  rm -rf /tmp/pantheon

  # Move to /tmp
  cd /tmp

  # Git clone from Pantheon
  git clone -b $CIRCLE_BRANCH ssh://codeserver.dev.$PANTHEON_SITE@codeserver.dev.$PANTHEON_SITE.drush.in:2222/~/repository.git pantheon

  # Delete everything except the .git dir
  rm -rf /tmp/pantheon/*

  # Copy "full repo" code over
  cp -rf /root/drops_8_composer/* /tmp/pantheon/

  # Move to pantheon dir
  cd /tmp/pantheon

  # Setup git user.email and user.name
  git config user.email "${GIT_EMAIL}" && git config user.name "${CIRCLE_USERNAME}"

  # testing
  echo ""
  echo "raw git commit msg":
  echo ""
  echo $GIT_COMMIT_DESC
  echo ""
  echo "Git commit msg: $GIT_COMMIT_DESC"
  echo ""
  echo "3rd option"
  echo ${GIT_COMMIT_DESC}
  echo ""
  echo "3rd option with quotes"
  echo "${GIT_COMMIT_DESC}"

  # Git add and commit
  git add -A
  #git commit -m "Circle CI Build: ${CIRCLE_BUILD_URL}"
  git commit -m "Circle CI Build: $CIRCLE_BUILD_URL"

  # Push code to multidev
  git push -f origin $CIRCLE_BRANCH

  # Run update.php
  terminus -n drush "$TERMINUS_SITE.$CIRCLE_BRANCH" -- updatedb -y

  # Run config-import -y
  terminus -n drush "$TERMINUS_SITE.$CIRCLE_BRANCH" -- config-import --yes

  # Clear drupal cache
fi

# Master branch
if [ "$CIRCLE_BRANCH" == "master" ]; then
  echo "$TERMINUS_SITE.dev will be updated."
  # Log in w/ terminus
  terminus -n auth:login --machine-token="$TERMINUS_TOKEN"

  # Remove any existing build
  rm -rf /tmp/pantheon

  # Move to /tmp
  cd /tmp

  # Git clone from Pantheon
  git clone ssh://codeserver.dev.$PANTHEON_SITE@codeserver.dev.$PANTHEON_SITE.drush.in:2222/~/repository.git pantheon

  # Delete everything except the .git dir
  rm -rf /tmp/pantheon/*

  # Copy "full repo" code over
  cp -rf /root/drops_8_composer/* /tmp/pantheon/

  # Move to pantheon dir
  cd /tmp/pantheon

  # Setup git user.email and user.name
  git config user.email "${GIT_EMAIL}" && git config user.name "${CIRCLE_USERNAME}"

  # testing
  echo "Git commit msg: $GIT_COMMIT_DESC"

  # Git add and commit
  git add -A
  #git commit -m "Circle CI Build: ${CIRCLE_BUILD_URL} Git commit msg: ${GIT_COMMIT_DESC}"
  git commit -m "Circle CI Build: $CIRCLE_BUILD_URL"

  # Push code to dev
  git push -f origin master

  # Run update.php
  terminus -n drush "$TERMINUS_SITE.$CIRCLE_BRANCH" -- updatedb -y

  # Run config-import -y
  # @todo need to make this not hardcoded as dev
  terminus -n drush $TERMINUS_SITE.dev -- config-import --yes

  # Clear drupal cache
fi
