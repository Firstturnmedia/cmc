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
    And I press "Submit"
    Then I should see "New submission added to CMC Example Webform Survey."

    # Check that contact was created

  Scenario: Create webform
    Given I am logged in as a user with the "Staff" role
    # Create webform
    When I go to "/admin/structure/webform/add"
    And I fill in "Title" with "Behat Webform Create Test"
    And I wait "2" seconds
    And I press "Save"
    Then I should see "Behat Webform Create Test created."

    # Add webform elements
    And I click "Build"
    And I wait "2" seconds

    # First Name
    And I click "Add element"
    And I wait for AJAX to finish
    And I click "Text field"
    And I wait for AJAX to finish
    And I fill in "Title" with "First Name"
    And I wait "2" seconds
    # Click save
    And I click on the element "#drupal-off-canvas .button--primary"
    And I wait for AJAX to finish
    Then I should see "first_name"

    # Last Name
    And I click "Add element"
    And I wait for AJAX to finish
    And I click "Text field"
    And I wait for AJAX to finish
    And I fill in "Title" with "Last Name"
    And I wait "2" seconds
    # Click save
    And I click on the element "#drupal-off-canvas .button--primary"
    And I wait for AJAX to finish
    Then I should see "last_name"

    # Email
    And I click "Add element"
    And I wait for AJAX to finish
    And I click "Email"
    And I wait for AJAX to finish
    And I fill in "Title" with "Email"
    And I wait "2" seconds
    # Click save
    And I click on the element "#drupal-off-canvas .button--primary"
    And I wait for AJAX to finish
    Then I should see "email"

    # Add checkboxes
    And I click "Add element"
    And I wait for AJAX to finish
    And I click "Checkboxes"
    And I wait for AJAX to finish
    And I fill in "Title" with "What is your favorite color?"
    And I wait "2" seconds
    # Fill in options
    And I fill in a field with "red" using css selector ".form-item-properties-options-custom-options-items-0-value input"
    And I fill in a field with "green" using css selector ".form-item-properties-options-custom-options-items-1-value input"
    And I fill in a field with "blue" using css selector ".form-item-properties-options-custom-options-items-2-value input"
    And I wait "2" seconds
    # Click save
    And I click on the element "#drupal-off-canvas .button--primary"
    And I wait for AJAX to finish
    Then I should see "What is your favorite color?"

    # Check webform elements are displaying on the front end
    When I click "View"
    And I wait "2" seconds
    Then I should see "First Name"
    And I should see "Last Name"
    And I should see "Email"
    And I should see "What is your favorite color?"
    And I should see "red"
    And I should see "green"
    And I should see "blue"

    # Add taxonomy config
    When I click "Build"
    And I wait "2" seconds
    And I click "Taxonomy Config"
    And I wait "2" seconds
    Then I should see "Taxonomy Config"
    And I should see "Taxonomy Vocabularies"
    When I check "Interests"
    And I check "Volunteerism/Engagement"
    # Click save
    And I click on the element ".cmc-webform-taxonomy-config #edit-submit"
    And I wait "2" seconds
    Then I should see "Taxonomy config has been saved."

    # Add taxonomy mapping
    When I click "Taxonomy Mapping"
    And I wait "2" seconds
    Then I should see "CMC Webform - Taxonomy fields mapping"
    When I select the first autocomplete option for "Social" on the "red" field
    And I wait "2" seconds
    # Click save
    And I click on the element ".cmc-webform-taxonomy-mapping #edit-submit"
    And I wait "2" seconds
    Then I should see "Taxonomy mappings have been saved."

    # Add handler
    When I click "Settings"
    And I wait "2" seconds
    And I click "Emails / Handlers"
    And I wait "2" seconds
    And I click "Add handler"
    And I wait for AJAX to finish
    And I click "Add Contact"
    And I wait for AJAX to finish
    And I select "email" from "Email"
    And I select "first_name" from "First Name"
    And I select "last_name" from "Last Name"
    # Click save
    And I click on the element "#drupal-off-canvas .button--primary"
    And I wait for AJAX to finish
    # @todo add summary to table, and test for that, better test that way!
    Then I should see "add_contact"
