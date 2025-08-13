# Authentication API Documentation

## Overview

This directory contains comprehensive documentation for the Authentication API system in the Laravel API SOLID application. The authentication system is built using Laravel Passport OAuth2, following SOLID principles and the Service-Repository pattern for clean, maintainable, and testable code.

## Architecture Overview

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   API Routes    │───▶│  AuthController  │───▶│   Services      │
│                 │    │                  │    │                 │
│ POST /login     │    │ - login()        │    │ - LoginService  │
│ POST /signup    │    │ - signup()       │    │ - SignupService │
│ POST /logout    │    │ - logout()       │    │                 │
│ GET  /me        │    │ - me()           │    │                 │
└─────────────────┘    └──────────────────┘    └─────────────────┘
                                │                        │
                                ▼                        ▼
                       ┌──────────────────┐    ┌─────────────────┐
                       │  Form Requests   │    │  UserRepository │
                       │                  │    │                 │
                       │ - LoginRequest   │    │ - findByField() │
                       │ - SignupRequest  │    │ - create()      │
                       └──────────────────┘    └─────────────────┘
                                                         │
                                                         ▼
                                                ┌─────────────────┐
                                                │   User Model    │
                                                │                 │
                                                │ - HasApiTokens  │
                                                │ - Passport      │
                                                └─────────────────┘
```

## Components

### 1. AuthController

**File**: `app/Http/Controllers/Api/AuthController.php`

The main controller handling all authentication endpoints:

- User login with token generation
- User registration with automatic login
- User logout with token revocation
- Authenticated user profile retrieval

**[📖 Full Documentation](./AuthController.md)**

### 2. Authentication Services

#### LoginService

**File**: `app/Services/Auth/Api/LoginService.php`

Handles user authentication business logic:

- Credential validation
- OAuth2 token generation
- Token expiration management
- User logout and token revocation

**[📖 Full Documentation](./LoginService.md)**

#### SignupService

**File**: `app/Services/Auth/Api/SignupService.php`

Handles user registration business logic:

- User account creation
- Email uniqueness validation
- Password hashing
- Automatic token generation for new users

**[📖 Full Documentation](./SignupService.md)**

### 3. Form Requests

#### LoginRequest & SignupRequest

**Files**:

- `app/Http/Requests/Auth/Api/LoginRequest.php`
- `app/Http/Requests/Auth/Api/SignupRequest.php`

Handle input validation and sanitization:

- Comprehensive validation rules
- Custom error messages
- Data preparation and sanitization
- Authorization logic

**[📖 Full Documentation](./Requests.md)**

### 4. Supporting Components

- **UserResource**: Transforms user data for API responses
- **UserRepository**: Handles user data access using Repository pattern
- **ApiResponseTrait**: Provides consistent API response formatting
- **Laravel Passport**: OAuth2 server implementation

## API Endpoints

### Public Endpoints

| Method | Endpoint              | Description       | Request Body                                                                                                                                             |
| ------ | --------------------- | ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------- |
| POST   | `/api/v1/auth/login`  | User login        | `{"email": "user@example.com", "password": "password123", "remember_me": true}` <!-- pragma: allowlist secret -->                                        |
| POST   | `/api/v1/auth/signup` | User registration | `{"name": "John Doe", "email": "user@example.com", "password": "password123", "password_confirmation": "password123"}` <!-- pragma: allowlist secret --> |

### Protected Endpoints (Require Authentication)

| Method | Endpoint              | Description      | Headers                         |
| ------ | --------------------- | ---------------- | ------------------------------- |
| POST   | `/api/v1/auth/logout` | User logout      | `Authorization: Bearer {token}` |
| GET    | `/api/v1/auth/me`     | Get user profile | `Authorization: Bearer {token}` |

## Quick Start Guide

### 1. User Registration

```bash
curl -X POST http://localhost:8000/api/v1/auth/signup \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123", // pragma: allowlist secret
    "password_confirmation": "password123" // pragma: allowlist secret
  }'
```

**Response**:

```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        },
        "token": {
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
            "token_type": "Bearer",
            "expires_at": "2024-01-15 12:00:00"
        }
    }
}
```

### 2. User Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123", // pragma: allowlist secret
    "remember_me": true
  }'
```

### 3. Access Protected Resources

```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer {access_token}" \
  -H "Accept: application/json"
```

### 4. User Logout

```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer {access_token}" \
  -H "Accept: application/json"
```

## JavaScript/Frontend Integration

### Using Axios

```javascript
// Configure axios defaults
axios.defaults.baseURL = "http://localhost:8000/api";
axios.defaults.headers.common["Accept"] = "application/json";
axios.defaults.headers.common["Content-Type"] = "application/json";

// Registration
async function register(userData) {
    try {
        const response = await axios.post("/v1/auth/signup", userData);
        const { token, user } = response.data.data;

        // Store token
        localStorage.setItem("access_token", token.access_token);
        localStorage.setItem("user", JSON.stringify(user));

        // Set default authorization header
        axios.defaults.headers.common["Authorization"] =
            `Bearer ${token.access_token}`;

        return { token, user };
    } catch (error) {
        throw error.response.data;
    }
}

// Login
async function login(credentials) {
    try {
        const response = await axios.post("/v1/auth/login", credentials);
        const { token, user } = response.data.data;

        localStorage.setItem("access_token", token.access_token);
        localStorage.setItem("user", JSON.stringify(user));
        axios.defaults.headers.common["Authorization"] =
            `Bearer ${token.access_token}`;

        return { token, user };
    } catch (error) {
        throw error.response.data;
    }
}

// Get user profile
async function getProfile() {
    try {
        const response = await axios.get("/v1/auth/me");
        return response.data.data;
    } catch (error) {
        throw error.response.data;
    }
}

// Logout
async function logout() {
    try {
        await axios.post("/v1/auth/logout");

        // Clear stored data
        localStorage.removeItem("access_token");
        localStorage.removeItem("user");
        delete axios.defaults.headers.common["Authorization"];
    } catch (error) {
        // Even if logout fails, clear local data
        localStorage.removeItem("access_token");
        localStorage.removeItem("user");
        delete axios.defaults.headers.common["Authorization"];
    }
}

// Initialize auth on app start
function initializeAuth() {
    const token = localStorage.getItem("access_token");
    if (token) {
        axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
    }
}

// Handle token expiration
axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Token expired or invalid
            localStorage.removeItem("access_token");
            localStorage.removeItem("user");
            delete axios.defaults.headers.common["Authorization"];

            // Redirect to login page
            window.location.href = "/login";
        }
        return Promise.reject(error);
    },
);
```

## Security Features

### 1. OAuth2 Authentication

- **Laravel Passport**: Industry-standard OAuth2 implementation
- **Personal Access Tokens**: Secure token-based authentication
- **Token Expiration**: Configurable token lifetimes
- **Token Revocation**: Complete logout with token invalidation

### 2. Password Security

- **Bcrypt Hashing**: Secure password hashing using Laravel's Hash facade
- **Minimum Length**: 8 character minimum requirement
- **Confirmation Required**: Password confirmation prevents typos
- **No Plain Text Storage**: Passwords are never stored in plain text

### 3. Input Validation

- **Comprehensive Validation**: All inputs validated using FormRequest classes
- **Email Uniqueness**: Prevents duplicate accounts
- **Format Validation**: Ensures proper email format
- **XSS Protection**: Laravel's built-in XSS protection

### 4. API Security

- **CORS Configuration**: Proper cross-origin resource sharing setup
- **Rate Limiting**: Can be configured to prevent brute force attacks
- **HTTPS Enforcement**: Should be used in production
- **Token Transmission**: Secure Bearer token format

## Error Handling

### Standard Error Response Format

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field_name": ["Specific error message"]
    }
}
```

### Common Error Scenarios

#### 1. Validation Errors (422)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["Please provide a valid email address"],
        "password": ["Password must be at least 8 characters"]
    }
}
```

#### 2. Authentication Errors (401)

```json
{
    "message": "Unauthenticated."
}
```

#### 3. Invalid Credentials (422)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

## Testing

The authentication system includes comprehensive tests:

### Feature Tests

- **AuthControllerTest**: Tests all controller endpoints
- **Authentication Flow**: Complete registration and login flows
- **Error Scenarios**: Invalid inputs and edge cases

### Unit Tests

- **LoginServiceTest**: Service layer logic testing
- **SignupServiceTest**: Registration business logic
- **Request Tests**: Validation rule testing
- **Resource Tests**: API response formatting

### Running Tests

```bash
# Run all authentication tests
php artisan test --filter=Auth

# Run specific test classes
php artisan test tests/Feature/AuthControllerTest.php
php artisan test tests/Unit/LoginServiceTest.php

# Run with coverage
php artisan test --coverage
```

## Configuration

### Environment Variables

```env
# Authentication Configuration
AUTH_GUARD=api
AUTH_PASSWORD_BROKER=users
AUTH_MODEL=App\Models\User

# Passport Configuration
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=1
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=your-secret-key
```

### Token Lifetimes

**File**: `app/Providers/AuthServiceProvider.php`

```php
// Configure token lifetimes
Passport::tokensExpireIn(now()->addDays(15));
Passport::refreshTokensExpireIn(now()->addDays(30));
Passport::personalAccessTokensExpireIn(now()->addMonths(6));
```

### CORS Configuration

**File**: `config/cors.php`

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:3000'], // Your frontend URL
'allowed_headers' => ['*'],
'supports_credentials' => true,
```

## Deployment Considerations

### Production Setup

1. **HTTPS Only**: Always use HTTPS in production
2. **Environment Variables**: Secure storage of secrets
3. **Token Cleanup**: Regular cleanup of expired tokens
4. **Rate Limiting**: Implement rate limiting for auth endpoints
5. **Monitoring**: Log authentication events for security monitoring

### Performance Optimization

1. **Database Indexing**: Index email field for fast lookups
2. **Token Caching**: Consider caching frequently accessed tokens
3. **Connection Pooling**: Use database connection pooling
4. **CDN**: Use CDN for static assets

## Troubleshooting

### Common Issues

1. **"Unauthenticated" Error**
    - Check Bearer token format
    - Verify token hasn't expired
    - Ensure Passport is properly configured

2. **Token Generation Fails**
    - Run `php artisan passport:keys`
    - Check database migrations
    - Verify Passport client exists

3. **CORS Issues**
    - Configure CORS properly
    - Check allowed origins
    - Verify preflight requests

### Debug Commands

```bash
# Check Passport status
php artisan passport:client --personal

# Generate new keys
php artisan passport:keys --force

# Check routes
php artisan route:list --path=auth

# Clear caches
php artisan cache:clear
php artisan config:clear
```

## Best Practices

1. **Always Use HTTPS**: Protect token transmission
2. **Implement Rate Limiting**: Prevent brute force attacks
3. **Regular Token Cleanup**: Remove expired tokens
4. **Monitor Authentication**: Log security events
5. **Validate All Inputs**: Use FormRequest classes
6. **Handle Errors Gracefully**: Provide meaningful error messages
7. **Test Thoroughly**: Comprehensive test coverage
8. **Document APIs**: Keep documentation up to date

## Related Documentation

- **[Laravel Passport Setup](../../general/passport-setup.md)**: Complete Passport configuration guide
- **[Service-Repository Pattern](../../general/service-repository-pattern.md)**: Architecture pattern explanation
- **[API Documentation](../../general/scramble-api-documentation.md)**: Interactive API documentation
- **[Code Quality Tools](../../general/code-quality-tools.md)**: Testing and quality assurance

## Support

For questions or issues related to the authentication system:

1. Check this documentation first
2. Review the test files for usage examples
3. Check Laravel Passport documentation
4. Review the application logs for error details

---

**Last Updated**: January 2024
**Version**: 1.0.0
**Laravel Version**: 11.x
**PHP Version**: 8.2+
