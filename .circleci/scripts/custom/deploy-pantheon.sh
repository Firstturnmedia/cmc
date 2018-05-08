#!/bin/bash

# Make sure feature branch names work with Pantheon's 11 char max.

# Multidev
if [ "$CIRCLE_BRANCH" != "master" ]; then
  # echo a sanity check that we're not on master
  echo "pwd is..."
  pwd
  echo "Branch is not master, so to the multidevs we go!"

  # Remove any existing build
  rm -rf /tmp/pantheon

  # Move to /tmp
  cd /tmp
  pwd

  # Git clone from Pantheon
  git clone -b $CIRCLE_BRANCH ssh://codeserver.dev.$PANTHEON_SITE@codeserver.dev.$PANTHEON_SITE.drush.in:2222/~/repository.git pantheon

  # Delete everything except the .git dir
  rm -rf /tmp/pantheon/*

  # Copy "full repo" code over
  cp -rf /home/circleci/lando/* /tmp/pantheon/

  # Move to pantheon dir
  cd /tmp/pantheon
  pwd

  git status
   
  # Log in w/ terminus
  #terminus -n auth:login --machine-token="$TERMINUS_TOKEN"
  echo "Terminus site : $TERMINUS_SITE"
  echo "Circle branch: $CIRCLE_BRANCH"
  echo "Show site multidevs"
  lando terminus multidev:list $TERMINUS_SITE
fi

# Master branch
if [ "$CIRCLE_BRANCH" == "master" ]; then
  echo "Master branch!"
  #echo "$TERMINUS_SITE.dev will be updated."
fi
