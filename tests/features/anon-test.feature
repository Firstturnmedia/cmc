Feature: Test access for anonymous users
  In order have a safe site
  As an anonymous visitor
  I need to see some page and be denied access to others.

  @javascript
  Scenario: Anonymous user can't create content
    Given I am an anonymous user
    And I am on "node/add/blog"
    And I wait "10" seconds
    Then I should see "You are not authorized to access this page."
