<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\MinkContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\AfterStepScope;

use Drupal\DrupalExtension\Context\RawDrupalContext;

/**
 * Define application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements Context, SnippetAcceptingContext {
  /**
   * Initializes context.
   * Every scenario gets its own context object.
   *
   * @param array $parameters
   *   Context parameters (set them in behat.yml)
   */
  public function __construct(array $parameters = []) {
    // Initialize your context here
  }

  /** @var \Drupal\DrupalExtension\Context\MinkContext */
  private $minkContext;
  /** @BeforeScenario */
  public function gatherContexts(BeforeScenarioScope $scope)
  {
      $environment = $scope->getEnvironment();
      $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }

//
// Place your definition and hook methods here:
//
//  /**
//   * @Given I have done something with :stuff
//   */
//  public function iHaveDoneSomethingWith($stuff) {
//    doSomethingWith($stuff);
//  }
//

    /**
     * Fills in form field with specified id|name|label|value
     * Example: And I enter the value of the env var "TEST_PASSWORD" for "edit-account-pass-pass1"
     *
     * @Given I enter the value of the env var :arg1 for :arg2
     */
    public function fillFieldWithEnv($value, $field)
    {
        $this->minkContext->fillField($field, getenv($value));
    }

    /**
     * @Given I wait for the progress bar to finish
     */
    public function iWaitForTheProgressBarToFinish() {
      $this->iFollowMetaRefresh();
    }

    /**
     * @Given I follow meta refresh
     *
     * https://www.drupal.org/node/2011390
     */
    public function iFollowMetaRefresh() {
      while ($refresh = $this->getSession()->getPage()->find('css', 'meta[http-equiv="Refresh"]')) {
        $content = $refresh->getAttribute('content');
        $url = str_replace('0; URL=', '', $content);
        $this->getSession()->visit($url);
      }
    }

    /**
     * @Given I have wiped the site
     */
    public function iHaveWipedTheSite()
    {
        $site = getenv('TERMINUS_SITE');
        $env = getenv('TERMINUS_ENV');

        passthru("terminus env:wipe $site.$env --yes");
    }

    /**
     * @Given I have reinstalled
     */
    public function iHaveReinstalled()
    {
        $site = getenv('TERMINUS_SITE');
        $env = getenv('TERMINUS_ENV');
        $site_name = getenv('TEST_SITE_NAME');
        $site_mail = getenv('ADMIN_EMAIL');
        $admin_password = getenv('ADMIN_PASSWORD');

        passthru("terminus --yes drush $site.$env -- --yes site-install standard --site-name=\"$site_name\" --site-mail=\"$site_mail\" --account-name=admin --account-pass=\"$admin_password\"'");
    }

    /**
     * @Given I have run the drush command :arg1
     */
    public function iHaveRunTheDrushCommand($arg1)
    {
        $site = getenv('TERMINUS_SITE');
        $env = getenv('TERMINUS_ENV');

        $return = '';
        $output = array();
        exec("terminus drush $site.$env -- " . $arg1, $output, $return);
        // echo $return;
        // print_r($output);

    }

    /**
     * @Given I have committed my changes with comment :arg1
     */
    public function iHaveCommittedMyChangesWithComment($arg1)
    {
        $site = getenv('TERMINUS_SITE');
        $env = getenv('TERMINUS_ENV');

        passthru("terminus --yes $site.$env env:commit --message='$arg1'");
    }

    /**
     * @Given I have exported configuration
     */
    public function iHaveExportedConfiguration()
    {
        $site = getenv('TERMINUS_SITE');
        $env = getenv('TERMINUS_ENV');

        $return = '';
        $output = array();
        exec("terminus drush $site.$env -- config-export -y", $output, $return);
    }

    /**
     * @Given I wait :seconds seconds
     */
    public function iWaitSeconds($seconds)
    {
        sleep($seconds);
    }

    /**
     * @Given I wait :seconds seconds or until I see :text
     */
    public function iWaitSecondsOrUntilISee($seconds, $text)
    {
        $errorNode = $this->spin( function($context) use($text) {
            $node = $context->getSession()->getPage()->find('named', array('content', $text));
            if (!$node) {
              return false;
            }
            return $node->isVisible();
        }, $seconds);

        // Throw to signal a problem if we were passed back an error message.
        if (is_object($errorNode)) {
          throw new Exception("Error detected when waiting for '$text': " . $errorNode->getText());
        }
    }

    // http://docs.behat.org/en/v2.5/cookbook/using_spin_functions.html
    // http://mink.behat.org/en/latest/guides/traversing-pages.html#selectors
    public function spin ($lambda, $wait = 60)
    {
        for ($i = 0; $i <= $wait; $i++)
        {
            if ($i > 0) {
              sleep(1);
            }

            $debugContent = $this->getSession()->getPage()->getContent();
            file_put_contents("/tmp/mink/debug-" . $i, "\n\n\n=================================\n$debugContent\n=================================\n\n\n");

            try {
                if ($lambda($this)) {
                    return true;
                }
            } catch (Exception $e) {
                // do nothing
            }

            // If we do not see the text we are waiting for, fail fast if
            // we see a Drupal 8 error message pane on the page.
            $node = $this->getSession()->getPage()->find('named', array('content', 'Error'));
            if ($node) {
              $errorNode = $this->getSession()->getPage()->find('css', '.messages--error');
              if ($errorNode) {
                return $errorNode;
              }
              $errorNode = $this->getSession()->getPage()->find('css', 'main');
              if ($errorNode) {
                return $errorNode;
              }
              return $node;
            }
        }

        $backtrace = debug_backtrace();

        throw new Exception(
            "Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . "()\n" .
            $backtrace[1]['file'] . ", line " . $backtrace[1]['line']
        );

        return false;
    }

    /**
     * @AfterStep
     */
    public function afterStep(AfterStepScope $scope)
    {
        // Do nothing on steps that pass
        $result = $scope->getTestResult();
        if ($result->isPassed()) {
            return;
        }

        // Otherwise, dump the page contents.
        $session = $this->getSession();
        $page = $session->getPage();
        $html = $page->getContent();
        $html = static::trimHead($html);

        print "::::::::::::::::::::::::::::::::::::::::::::::::\n";
        print $html . "\n";
        print "::::::::::::::::::::::::::::::::::::::::::::::::\n";
    }

    /**
     * Remove everything in the '<head>' element except the
     * title, because it is long and uninteresting.
     */
    protected static function trimHead($html)
    {
        $html = preg_replace('#\<head\>.*\<title\>#sU', '<head><title>', $html);
        $html = preg_replace('#\</title\>.*\</head\>#sU', '</title></head>', $html);
        return $html;
    }

  /* @todo delete everything above? */

  /**
   * Entities by entity type and key.
   *
   * $entities array
   *   To delete after scenario.
   *
   * @var array
   */
  //protected $entities = [];

  /**
   * Remove contacts that were created during behat testing.
   *
   * @AfterScenario
   */
  public function cleanupContact() {
    $email = 'behat@firstturnmedia.com';

    // Get contact from email address
    $contact_id = db_query("SELECT id FROM {redhen_contact} WHERE email = :email",
      array(
        ':email' => $email)
      )->fetchField();

    if (!empty($contact_id)) {
      // Load the contact
      $contact = \Drupal::entityTypeManager()->getStorage('redhen_contact')->load($contact_id);
      // Delete the contact
      if (!is_null($contact)) {
        $contact->delete();
      }
    }
  }

  /**
   * Remove webform that was created during testing.
   *
   * @AfterScenario
   */
  public function cleanupWebform() {
    $webform_id = 'behat_webform_create_test';
    // Load the webform
    $webform_entity = \Drupal::entityTypeManager()->getStorage('webform')->load($webform_id);
    // Delete webform
    if (!is_null($webform_entity)) {
      $webform_entity->delete();
    }

    // Delete webform related config
    \Drupal::configFactory()->getEditable('cmc_webform.taxonomy_config.behat_webform_create_test')->delete();
    \Drupal::configFactory()->getEditable('cmc_webform.taxonomy_mapping.behat_webform_create_test')->delete();
  }

  /**
   * Click an element
   *
   * @param $selector string
   *   The css selector of the element.
   *
   * @When /^I click on the element "([^"]*)"$/
   */
  public function iClickOnTheElement($selector) {
    $session = $this->getSession();
    $element = $session->getPage()->find(
      'xpath',
      $session->getSelectorsHandler()->selectorToXpath('css', $selector)
    );
    if (null === $element) {
      throw new \InvalidArgumentException(sprintf('Cannot find element: "%s"', $selector));
    }

    $element->click();

  }

  /**
   * @When I fill in a field with :arg1 using css selector :arg2
   */
  public function fillFieldUsingCss($value, $selector) {
    $element = $this->getSession()->getPage()->find('css', $selector);
    if (null === $element) {
      throw new ElementNotFoundException($this->getSession(), 'form field', 'css selector', $selector);
    }

    $element->setValue($value);
  }

  /**
   * @When I fill in the autocomplete :autocomplete with :text and click :popup
   */
  public function fillInDrupalAutocomplete($autocomplete, $text, $popup) {
    //$el = $this->getSession()->getPage()->findField($autocomplete);
    $el = $this->getSession()->getPage()->find('css', $autocomplete);
    $el->focus();

    // Set the autocomplete text then put a space at the end which triggers
    // the JS to go do the autocomplete stuff.
    $el->setValue($text);
    $el->keyUp(' ');

    // Sadly this grace of 1 second is needed here.
    $this->wait(2);
    $this->minkContext->iWaitForAjaxToFinish();

    // Drupal autocompletes have an id of autocomplete which is bad news
    // if there are two on the page.
    /*$autocomplete = $this->getSession()->getPage()->findById('autocomplete');

    if (empty($autocomplete)) {
      throw new ExpectationException(t('Could not find the autocomplete popup box'), $this->getSession());
    }

    $popup_element = $autocomplete->find('xpath', "//div[text() = '{$popup}']");

    if (empty($popup_element)) {
      throw new ExpectationException(t('Could not find autocomplete popup text @popup', array(
        '@popup' => $popup)), $this->getSession());
    }

    $popup_element->click();*/

    $session = $this->getSession();
    $xpath = $el->getXpath();
    $driver = $session->getDriver();

    // Press the down arrow to select the first option.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);

    $this->wait(2);
    $this->minkContext->iWaitForAjaxToFinish();

    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);
  }

  /**
   * @When I select the first autocomplete option for :text on the :field field
   */
  public function iSelectFirstAutocomplete($text, $field) {
    $session = $this->getSession();
    $page = $session->getPage();
    $element = $page->findField($field);

    if (empty($element)) {
      throw new \Exception(sprintf('We couldn\'t find "%s" on the page', $field));
    }
    $page->fillField($field, $text);

    $xpath = $element->getXpath();
    $driver = $session->getDriver();

    // autocomplete.js uses key down/up events directly.

    // Press the backspace key.
    $driver->keyDown($xpath, 8);
    $driver->keyUp($xpath, 8);

    // Retype the last character.
    $chars = str_split($text);
    $last_char = array_pop($chars);
    $driver->keyDown($xpath, $last_char);
    $driver->keyUp($xpath, $last_char);

    // Wait for AJAX to finish.
    $this->minkContext->iWaitForAjaxToFinish();

    // And make sure the autocomplete is showing.
    $this->getSession()->wait(5000, 'jQuery("#autocomplete").show().length > 0');

    // And wait for 1 second just to be sure.
    sleep(1);

    // Press the down arrow to select the first option.
    $driver->keyDown($xpath, 40);
    $driver->keyUp($xpath, 40);

    // Press the Enter key to confirm selection, copying the value into the field.
    $driver->keyDown($xpath, 13);
    $driver->keyUp($xpath, 13);

    // Wait for AJAX to finish.
    $this->minkContext->iWaitForAjaxToFinish();
  }


}
