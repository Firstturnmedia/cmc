@javascript
@api
Feature: Blog Content Type
  As a Staff I want to test that the blog content type is functional

  Background:
    Given I am logged in as a user with the "Staff" role

  # Check that blog fields are avail
  Scenario: Check blog fields
    Given I go to "/node/add/blog"
    Then I should see "Create blog"
    And I should see "Title"
    And I should see "Image"
    And I should see "Body"
    And I should see "TAGS"

  # Test creating a blog post
  Scenario: Create blog
    Given I go to "/node/add/blog"
    And I fill in "Title" with "Behat Blog Test"
    And I fill in "Body" with "Behat Blog Test Body"
    And I attach the file "images/image.jpg" to "Image"
    And I wait "2" seconds
    And I fill in "Alternative text" with "Image alt text"
    When I press "Save"
    And I wait "2" seconds
    Then I should see "Behat Blog Test has been created."
    And I should see "Behat Blog Test Body"
    And I should see an "article img" element
    And I wait "2" seconds
