@javascript
@api
Feature: Webform Submission
  As an anonymous user I need to know if my webform submission works

  Background:
    Given I am an anonymous user

  Scenario: Webform Submission
    Given I go to "/form/cmc-example-webform-survey"
    And I fill in "First Name" with "Behat First Name"
    And I fill in "Last Name" with "Behat Last Name"
    And I fill in "Email" with "behat@firstturnmedia.com"
    And I check "Political"
    And I check "Edit a production"
    And I wait "5" seconds
    And I press "Submit"
    And I wait "3" seconds
    Then I should see "New submission added to CMC Example Webform Survey."

    # Check that contact was created
