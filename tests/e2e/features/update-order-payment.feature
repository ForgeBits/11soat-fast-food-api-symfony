Feature: Update order payment status
  In order to confirm a payment
  As an API client
  I want to update the payment status of an order

  Background:
    Given I set request header "Content-Type" to "application/json"

  Scenario: Mark order as paid
    Given I have the JSON payload:
      """
      { "status": "PAID" }
      """
    When I send a PATCH request to "/api/orders/1/status"
    Then the response status code should be 200
    And the JSON response property "status" should be "PAID"
