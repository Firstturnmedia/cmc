@javascript
@api
Feature: Blog Content Type
  As an admin I want to test that the blog content type is functional

  Scenario: Create blog
    Given I am logged in as a user with the "Administrator" role
    # Add blog
    When I go to "node/add/blog"
    And I fill in "Title" with "Behat Blog Test"
    And I fill in "Body" with "Behat Blog Test Body"
    And I attach the file "images/image.jpg" to "Image"
    And I wait "2" seconds
    And I fill in "Alternative text" with "Image alt text"
    And I press "Save"
    And I wait "2" seconds
    Then I should see "Behat Blog Test has been created."
    And I should see "Behat Blog Test Body"
    And I should see an "article img" element
    And I wait "10" seconds
