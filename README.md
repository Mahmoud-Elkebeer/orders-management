# Order and Payment Management API

## Overview
This Laravel-based API provides a robust order and payment management system with a focus on clean code principles and extensibility. The system allows seamless integration of new payment gateways with minimal code changes.

## Features

### Order Management
- **Create Order**: Accepts user details, purchased items (product name, quantity, price), and calculates the total.
- **Update Order**: Allows modifying existing order details.
- **Delete Order**: Orders can be deleted only if no payments are associated.
- **View Orders**: Retrieve all orders or filter by status (pending, confirmed, cancelled).

### Payment Management
- **Process Payment**: Simulates payment processing for an order, supporting multiple payment methods (credit_card, PayPal, etc.).
- **View Payments**: Retrieve payment details for a specific order or all payments.

### Business Rules
- Payments can only be processed for orders in the confirmed status.
- Orders cannot be deleted if they have associated payments.

## Installation
1. Clone the repository:
   ```bash
   git clone git@github.com:Mahmoud-Elkebeer/orders-management.git
   cd orders-management
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Copy the environment file:
   ```bash
   cp .env.example .env
   ```
4. Set up the database connection in the `.env` file:
    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_database_name
    DB_USERNAME=your_database_user
    DB_PASSWORD=your_database_password
    ```
5.  Generate application key:
   ```bash
   php artisan key:generate
   ```
6. Migrate tables:
   ```bash
   php artisan migrate
   ```
7. Run the application:
   ```bash
   php artisan serve
   ```

## Authentication
- Secure APIs using JWT authentication.
- Register and login endpoints provided.

## API Documentation
- API endpoints follow RESTFul principles.
- Postman collection included for easy testing.
- Pagination implemented for list endpoints.

## Testing
- Feature tests cover order management, payment processing.
- Unit tests ensure payment gateway logic works as expected.
- Run tests using:
  ```bash
  php artisan test
  ```

## Extensibility
- Uses the **Factory Pattern** for adding new payment gateways.
- Steps to add a new payment gateway:
    1. Create a new class in `app/Services/Payments/Gateways/` implementing `PaymentGatewayInterface`.
    2. Implement the `processPayment()` method.
    3. Add the gateway in `PaymentGatewayFactory.php`.

