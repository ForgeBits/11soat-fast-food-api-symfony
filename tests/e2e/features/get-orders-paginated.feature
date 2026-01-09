Feature: Get orders paginated
  In order to browse orders
  As an API client
  I want to list orders with pagination

  Scenario: List first page of orders
    Given I set request header "Accept" to "application/json"
    When I send a GET request to "/api/orders?page=1&limit=10"
    Then the response status code should be 200
    And the JSON response should have property "data"
    And the JSON response should have property "total"
