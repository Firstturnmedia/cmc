@javascript
@api
Feature: Redhen: Contact
  As a Staff user I need to create Redhen Contacts

  Scenario: Check contact permissions
    Given I go to "admin/structure/redhen_contact/add"
    Then I should see "Access denied"
