@javascript
Feature: Apply ACL permissions on mass edit actions
  In order to let users use mass edit actions
  As an administrator
  I need to be able manage ACL on mass edit actions

  Background:
    Given an "apparel" catalog configuration
    And the following products:
      | sku          | family  |
      | kickers      | sandals |
      | hiking_shoes | sandals |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-5171
  Scenario: View only the mass edit operations I have permissions on
    Given I edit the "Catalog manager" Role
    And I visit the "Permissions" tab
    And I grant rights to group Products
    And I revoke rights to resource Change product family and Change state of product
    And I save the Role
    Then I should not see the text "There are unsaved changes."
    When I am on the products page
    And I mass-edit products kickers and hiking_shoes
    Then I should see the text "Edit common attributes"
    And I should see the text "Classify products in categories"
    And I should see the text "Add to groups"
    And I should see the text "Add to a variant group"
    And I should not see "Change the family of products"
    And I should not see "Change status (enable / disable)"

  Scenario: View all mass edit operations
    Given I am on the products page
    When I mass-edit products kickers and hiking_shoes
    Then I should see the text "Change status (enable / disable)"
    And I should see the text "Edit common attributes"
    And I should see the text "Classify products in categories"
    And I should see the text "Change the family of products"
    And I should see the text "Add to groups"
    And I should see the text "Add to a variant group"
