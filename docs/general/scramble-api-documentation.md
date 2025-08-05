# Scramble API Documentation Setup

This document provides comprehensive information about the Scramble API documentation setup in this Laravel project.

## Overview

Scramble is an automatic OpenAPI documentation generator for Laravel that creates API documentation without requiring manual annotations. It analyzes your Laravel routes, controllers, FormRequests, and API Resources to generate comprehensive API documentation.

## Installation

Scramble has been installed and configured in this project:

```bash
composer require dedoc/scramble
php artisan vendor:publish --provider="Dedoc\Scramble\ScrambleServiceProvider"
```

## Configuration

The Scramble configuration is located at `config/scramble.php` and includes:

### API Information

- **Title**: Laravel API SOLID
- **Version**: 1.0.0 (configurable via `API_VERSION` environment variable)
- **Description**: Laravel API with SOLID principles - Auto-generated documentation using Scramble
- **Contact**: Alaa Nabawii (contact@example.com)

### UI Configuration

- **Theme**: Light theme
- **Layout**: Responsive layout
- **Title**: Laravel API SOLID - Documentation
- **Try It**: Enabled for testing endpoints

### API Path Configuration

- **API Path**: `api` (all routes starting with `/api` are documented)
- **Export Path**: `api.json` (OpenAPI specification file)

## Project Structure

The following components have been created to demonstrate Scramble's capabilities:

### Controllers

#### UserController (`app/Http/Controllers/Api/UserController.php`)

A complete CRUD controller for user management with the following endpoints:

- `GET /api/v1/users` - List users with search and pagination
- `POST /api/v1/users` - Create a new user
- `GET /api/v1/users/{user}` - Show a specific user
- `PUT/PATCH /api/v1/users/{user}` - Update a user
- `DELETE /api/v1/users/{user}` - Delete a user

### Form Requests

#### StoreUserRequest (`app/Http/Requests/StoreUserRequest.php`)

Validation rules for creating users:

- `name`: Required string, max 255 characters
- `email`: Required, unique email address
- `password`: Required, confirmed, follows Laravel password rules

#### UpdateUserRequest (`app/Http/Requests/UpdateUserRequest.php`)

Validation rules for updating users:

- `name`: Optional string, max 255 characters
- `email`: Optional, unique email address (ignores current user)
- `password`: Optional, confirmed, follows Laravel password rules

### API Resources

#### UserResource (`app/Http/Resources/UserResource.php`)

Transforms user data for API responses:

- Basic user information (id, name, email, timestamps)
- Conditional fields based on authentication
- Proper date formatting (ISO strings)

## API Routes

The API routes are defined in `routes/api.php`:

### Public Routes

- `GET /api/health` - Health check endpoint
- `GET|POST|PUT|DELETE /api/v1/users` - User CRUD operations

### Authenticated Routes

- `GET /api/user` - Get current authenticated user

## Accessing Documentation

Once the Laravel server is running, you can access the API documentation at:

```
http://localhost:8000/docs/api
```

### Features Available in the Documentation

1. **Interactive API Explorer**: Test endpoints directly from the documentation
2. **Request/Response Examples**: Automatically generated based on your code
3. **Validation Rules**: Displayed based on FormRequest classes
4. **Response Schemas**: Generated from API Resource classes
5. **Authentication Information**: Shows required authentication for protected routes

## How Scramble Works

Scramble automatically analyzes your Laravel application to generate documentation:

### Route Discovery

- Scans all routes starting with the configured API path (`/api`)
- Identifies HTTP methods and parameters
- Detects route model binding

### Request Analysis

- Reads FormRequest validation rules
- Generates request schemas with required/optional fields
- Includes validation messages and rules

### Response Analysis

- Analyzes API Resource classes
- Detects conditional fields (`mergeWhen`, `when`)
- Generates response schemas with proper types

### Controller Method Documentation

- Uses method names and comments for endpoint descriptions
- Detects return types for response documentation
- Identifies middleware and authentication requirements

## Best Practices Implemented

### 1. Use FormRequest Classes

```php
public function store(StoreUserRequest $request): UserResource
{
    // Scramble automatically documents validation rules
    $user = User::create($request->validated());
    return new UserResource($user);
}
```

### 2. Use API Resources

```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            // Conditional fields are automatically documented
            $this->mergeWhen($condition, ['extra_field' => 'value']),
        ];
    }
}
```

### 3. Proper Return Type Hints

```php
public function index(Request $request): AnonymousResourceCollection
public function show(User $user): UserResource
public function destroy(User $user): JsonResponse
```

### 4. Route Model Binding

```php
// Automatically documented as {user} parameter
Route::apiResource('users', UserController::class);
```

## Customization Options

### Environment Variables

Add to your `.env` file:

```env
API_VERSION=1.0.0
```

### Middleware Configuration

The documentation is protected by:

- `web` middleware
- `RestrictedDocsAccess` middleware (restricts access in production)

### Extending Documentation

You can extend Scramble's functionality by:

1. Adding custom extensions in the config
2. Using Scramble's API to add custom documentation
3. Implementing custom route resolvers

## Production Considerations

### Security

- Documentation access is restricted in production by default
- Consider adding authentication middleware for sensitive APIs
- Review the `RestrictedDocsAccess` middleware configuration

### Performance

- Documentation is generated on-demand
- Consider caching the generated OpenAPI specification
- Use `php artisan scramble:export` to generate static documentation

### Deployment

```bash
# Generate static documentation for production
php artisan scramble:export
```

## Troubleshooting

### Common Issues

1. **Routes not appearing**: Ensure routes start with the configured `api_path`
2. **Validation rules not showing**: Use FormRequest classes instead of inline validation
3. **Response schemas missing**: Use API Resources instead of raw arrays
4. **Authentication not documented**: Ensure middleware is properly configured

### Debug Commands

```bash
# List all routes that Scramble will document
php artisan route:list --path=api

# Clear route cache if routes aren't updating
php artisan route:clear

# Export OpenAPI specification
php artisan scramble:export
```

## Additional Resources

- [Scramble Documentation](https://scramble.dedoc.co/)
- [OpenAPI Specification](https://swagger.io/specification/)
- [Laravel API Resources](https://laravel.com/docs/eloquent-resources)
- [Laravel Form Requests](https://laravel.com/docs/validation#form-request-validation)

## Conclusion

Scramble provides an excellent developer experience with minimal setup overhead. By following Laravel best practices with FormRequests and API Resources, you get comprehensive, automatically updated API documentation that stays in sync with your code.

The implementation in this project demonstrates:

- Automatic endpoint discovery
- Request/response schema generation
- Validation rule documentation
- Interactive API testing
- Professional documentation UI

This approach eliminates the need for manual annotation maintenance while providing rich, accurate API documentation.
