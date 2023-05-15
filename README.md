# Budget

Provides REST API for managing budget application.
Functionality includes:

- user registration, and login
- category creation
- transaction creation
- transaction summary endpoint
- user status endpoint

Swagger documentation can be found at /api/doc

Project can be set up locally using Warden (all docker containers and host setup) - https://docs.warden.dev/

Swagger documentation is available at `/api/doc`. Swagger has some issues with making requests with query parameters having the same name, so postman collection can be used instead (available in the repository).

Run tests via `make tests`