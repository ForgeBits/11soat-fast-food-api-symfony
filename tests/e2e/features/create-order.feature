Feature: Create order
  In order to manage orders
  As an API client
  I want to create a new order

  Scenario: Successfully create a new order
    Given I set request header "Content-Type" to "application/json"
    And I have the JSON payload:
      """
      {
        "clientId": 123,
        "codeClientRandom": 456,
        "isRandomClient": true,
        "amount": 59.80,
        "observation": "Sem cebola no X-Bacon e alface extra",
        "transactionId": "b5e1a1a8-6c2c-4c1d-9f7a-9a1f2d3c4b5eee",
        "items": [
          {
            "productId": 1,
            "title": "X-Bacon",
            "quantity": 1,
            "price": 29.90,
            "observation": "Ponto bem passado",
            "customerItems": [
              {
                "itemId": 6,
                "title": "Alface extra",
                "quantity": 5,
                "price": 2.50,
                "observation": "Folhas maiores"
              },
              {
                "itemId": 7,
                "title": "Molho especial",
                "quantity": 2,
                "price": 1.90,
                "observation": null
              }
            ]
          }
        ]
      }
      """
    When I send a POST request to "/api/orders"
    Then the response status code should be 200
    And the JSON response should have property "id"
    And the JSON response property "status" should be "CREATED"
