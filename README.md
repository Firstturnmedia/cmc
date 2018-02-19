# CMC Drupal 8

## Circle CI
[![CircleCI](https://circleci.com/gh/Firstturnmedia/cmc.svg?style=svg)](https://circleci.com/gh/Firstturnmedia/cmc)

## Pantheon
[Pantheon Dashboard](https://dashboard.pantheon.io/sites/433e8db8-16c3-441e-bc3b-e533ede92147)

## Code Maintenance
```drush make```, ```drush pm-download```, ```drush pm-update``` and their like are the old-school way of maintaining your code base. Do not use these, use composer instead.

Composer Guide:

| Task                                            | Drush                                         | Composer                                          |
|-------------------------------------------------|-----------------------------------------------|---------------------------------------------------|
| Installing a contrib project (latest version)   | ```drush pm-download PROJECT```               | ```composer require drupal/PROJECT```             |
| Installing a contrib project (specific version) | ```drush pm-download PROJECT-8.x-1.0-beta3``` | ```composer require drupal/PROJECT:1.0.0-beta3``` |
| Installing a javascript library (e.g. dropzone) | ```drush pm-download dropzone```              | ```composer require bower-asset/dropzone```       |
| Updating all contrib projects and Drupal core   | ```drush pm-update```                         | ```composer update```                             |
| Updating a single contrib project               | ```drush pm-update PROJECT```                 | ```composer update drupal/PROJECT```              |
| Updating Drupal core                            | ```drush pm-update drupal```                  | ```composer update drupal/core```                 |

The magic is that Composer, unlike Drush, is a *dependency manager*. If module ```foo version: 1.0.0``` depends on ```baz version: 3.2.0```, Composer will not let you update baz to ```3.3.0``` (or downgrade it to ```3.1.0```, for that matter). Drush has no concept of dependency management. If you've ever accidentally hosed a site because of dependency issues like this, you've probably already realized how valuable Composer can be.

But to be clear: it is still very helpful to use a site management tool like Drush or Drupal Console. Tasks such as database updates (```drush updatedb```) are still firmly in the province of such utilities. This installer will install a copy of Drush (local to the project) in the ```bin``` directory.

### Specifying a version
you can specify a version from the command line with:

    $ composer require drupal/<modulename>:<version>

For example:

    $ composer require drupal/ctools:3.0.0-alpha26
    $ composer require drupal/token:1.x-dev

In these examples, the composer version 3.0.0-alpha26 maps to the drupal.org version 8.x-3.0-alpha26 and 1.x-dev maps to 8.x-1.x branch on drupal.org.

If you specify a branch, such as 1.x you must add -dev to the end of the version.

**Composer is only responsible for maintaining the code base**.

## Applying patches with composer

*In the below example, we are patching the config_ignore module.*

#### Add patch to composer.json

```javascript
"extra": {
  "patches": {
    // Module to patch
    "drupal/config_ignore": {
      // Description: Link to patch on drupal.org
      "2857247 - Support for export filtering via Drush": "https://www.drupal.org/files/issues/support_for_export-2857247-12.patch"
    }
  }
}```

#### Run Composer Update

    $ composer update drupal/config_ignore


## Local Behat Testing

#### Start Selenium and Chromedriver

    $ start-sel

#### Run behat tests locally

    $ cd tests && ../vendor/bin/behat
