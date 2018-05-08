#!/bin/bash

# Make sure feature branch names work with Pantheon's 11 char max.

# Multidev
if [ "$CIRCLE_BRANCH" != "master" ]; then
  # Check if multidev aleeady exists. If not, create, else, update existing
  if ! lando terminus multidev:list $TERMINUS_SITE --field id | grep $CIRCLE_BRANCH; then
    echo "Creating multidev $CIRCLE_BRANCH-$TERMINUS_SITE.dev"
    lando terminus env:wake "$TERMINUS_SITE.dev"
    lando terminus multidev:create $TERMINUS_SITE.dev $CIRCLE_BRANCH
    else
      echo "Existing multidev $CIRCLE_BRANCH-$TERMINUS_SITE will be updated."
  fi

  # Get the latest commit msg
  GIT_COMMIT_MSG="$(git log -1 --pretty=%B)"

  # Remove any existing build
  rm -rf /tmp/pantheon

  # Move to /tmp
  cd /tmp

  # Git clone from Pantheon
  git clone -b $CIRCLE_BRANCH ssh://codeserver.dev.$PANTHEON_SITE@codeserver.dev.$PANTHEON_SITE.drush.in:2222/~/repository.git pantheon

  # Delete everything except the .git dir
  rm -rf /tmp/pantheon/*

  # Copy "full repo" code over
  cp -rf /home/circleci/lando/. /tmp/pantheon/

  # Move to pantheon dir
  cd /tmp/pantheon

  # Setup git user.email and user.name
  git config user.email "${GIT_EMAIL}" && git config user.name "${CIRCLE_USERNAME}"

  # Git add and commit
  git status
  git add .
  git commit -m "Circle CI Build: $CIRCLE_BUILD_URL" -m "- $GIT_COMMIT_MSG"

  # Push code to multidev
  if git push -f origin $CIRCLE_BRANCH
  then
    echo "Code pushed to Pantheon"
  else
    exit 1
  fi

  # Go back to lando dir for drush commands
  cd /home/circleci/lando/

  # Run update.php
  lando drush @pantheon.$TERMINUS_SITE.$CIRCLE_BRANCH updb -y

  # Run config import
  lando drush @pantheon.$TERMINUS_SITE.$CIRCLE_BRANCH cim -y

  # Clear drupal cache
  lando drush @pantheon.$TERMINUS_SITE.$CIRCLE_BRANCH cr all
fi

# Master branch
if [ "$CIRCLE_BRANCH" == "master" ]; then
  echo "Master branch!"
  #echo "$TERMINUS_SITE.dev will be updated."
fi
