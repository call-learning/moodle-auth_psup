@auth @auth_psup
Feature: The user should be logged in directly when signing up.

  Background:
    Given the following config values are set as admin:
      | registerauth | psup |
    And the following config values are set as admin:
      | currentsession | 2023 | auth_psup |
    And the following "users" exist:
      | username    | firstname | lastname | email           | profile_field_psupid | profile_field_psupsession | auth |
      | 101010_2023 | John      | Doe      | s1@example.com  | 101010               | 2023                      | psup |
      | 101010_2024 | John      | Doe2     | s10@example.com | 101010               | 2024                      | psup |

  Scenario: As a user I want to create a new account
    Given I am on site homepage
    And I follow "Log in"
    When I follow "Create new account"
    And I set the following fields to these values:
      | Parcoursup Identifier | 0011010               |
      | Password              | P@ssword#101A         |
      | Email address         | user1@address.invalid |
      | First name            | User1                 |
      | Last name             | L1                    |
    And I press "Create my new account"
    And I should see "An email should have been sent to your address at user1@address.invalid"
    And I should see "Continue"
    And I press "Continue"
    Then I should see "Welcome, User1!"

  Scenario Outline: Field validation during registration registration
    Given I am on site homepage
    And I follow "Log in"
    When I follow "Create new account"
    And I set the following fields to these values:
      | Parcoursup Identifier | <psupid>      |
      | Password              | P@ssword#101A |
      | Email address         | <email>       |
      | First name            | Jane          |
      | Last name             | Doe           |
    And I press "Create my new account"
    Then I should <expectpsupidsame> "A user with the same Parcoursup ID has already registered"
    And I should <expectpsupidwrong> "Invalid Parcoursup Identifier"
    And I should <expectreg> "This email address is already registered. Perhaps you created an account in the past?"
    And I should <expectemwrong> "Invalid email address"

    Examples:
      | psupid     | email          | expectreg | expectemwrong | expectpsupidsame | expectpsupidwrong |
      | 1000000123 | s5@example.com | not see   | not see       | not see          | see               |
      | 101010     | s4@example.com | not see   | not see       | see              | not see           |
      | p%30303    | S2@EXAMPLE.COM | not see   | not see       | not see          | see               |
      | P12330302  | S2@EXAMPLE.COM | not see   | not see       | not see          | see               |
      | P1000001   | s3@EXAMPLE.COM | not see   | not see       | not see          | see               |
      | 1000000    | s1             | not see   | see           | not see          | not see           |
      | 1000000    | s1@example.com | see       | not see       | not see          | not see           |
