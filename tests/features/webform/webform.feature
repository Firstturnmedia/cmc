@javascript
@api
Feature: Webform Submission
  Various

  Scenario: Webform Submission
    Given I am an anonymous user
    And I go to "/form/cmc-example-webform-survey"
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

  Scenario: Create webform
    Given I am logged in as a user with the "Staff" role
    # Create webform
    When I go to "/admin/structure/webform/add"
    And I fill in "Title" with "Behat Webform Create Test"
    And I wait "2" seconds
    And I press "Save"
    And I wait "2" seconds
    Then I should see "Behat Webform Create Test created."

    # Add taxonomy config

    # Add taxonomy mapping

    # Add handler
    And I click "Settings"
    And I wait "2" seconds
    And I click "Emails / Handlers"
    And I wait "5" seconds
    And I click "Add handler"
    And I wait for AJAX to finish
    And I click "Add Contact"
    And I wait for AJAX to finish
    # doesnt work below
    # And I click "Save"
