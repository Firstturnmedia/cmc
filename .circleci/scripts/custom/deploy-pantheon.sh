#!/bin/bash

# Make sure feature branch names work with Pantheon's 11 char max.

# Multidev
if [ "$CIRCLE_BRANCH" != "master" ]; then
  # echo a sanity check that we're not on master
  echo "Branch is not master, so to the multidevs we go!"
  # Log in w/ terminus
  #terminus -n auth:login --machine-token="$TERMINUS_TOKEN"
  echo "Terminus site : $TERMINUS_SITE"
  echo "Circle branch: $CIRCLE_BRANCH"
  echo "Show site multidevs"
  terminus multidev:list $TERMINUS_SITE
fi

# Master branch
if [ "$CIRCLE_BRANCH" == "master" ]; then
  echo "Master branch!"
  #echo "$TERMINUS_SITE.dev will be updated."
fi
