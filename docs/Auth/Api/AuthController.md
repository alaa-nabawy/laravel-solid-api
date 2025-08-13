# AuthController Documentation

## Overview

The `AuthController` is responsible for handling all authentication-related operations in the Laravel API SOLID application. It provides endpoints for user registration, login, logout, and retrieving authenticated user information.

**Location**: `app/Http/Controllers/Api/AuthController.php`

## Features

- **User Registration**: Create new user accounts with validation
- **User Login**: Authenticate users and issue OAuth2 tokens
- **User Logout**: Revoke user access tokens
- **User Profile**: Retrieve authenticated user information
- **Laravel Passport Integration**: OAuth2 authentication with personal access tokens
- **Comprehensive Error Handling**: Validation and exception handling
- **API Response Standardization**: Consistent response format using ApiResponseTrait

## Dependencies

- **Services**: `LoginService`, `SignupService`
- **Requests**: `LoginRequest`, `SignupRequest`
- **Resources**: `UserResource`
- **Traits**: `ApiResponseTrait`
- **Authentication**: Laravel Passport OAuth2

## Endpoints

### 1. User Login

**Endpoint**: `POST /api/v1/auth/login`

**Description**: Authenticates a user and returns an access token.

**Request Body**:

```json
{
    "email": "user@example.com",
    "password": "password123", // pragma: allowlist secret
    "remember_me": true
}
```

**Validation Rules**:

- `email`: Required, valid email format
- `password`: Required string
- `remember_me`: Optional boolean (extends token expiration)

**Success Response** (200):

```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": {
            "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
            "token_type": "Bearer",
            "expires_at": "2024-01-15 12:00:00"
        },
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "user@example.com",
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    }
}
```

**Error Response** (422):

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

### 2. User Registration

**Endpoint**: `POST /api/v1/auth/signup`

**Description**: Registers a new user and returns an access token.

**Request Body**:

```json
{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123", // pragma: allowlist secret
    "password_confirmation": "password123" // pragma: allowlist secret
}
```

**Validation Rules**:

- `name`: Required string, max 255 characters
- `email`: Required, valid email, unique in users table
- `password`: Required string, min 8 characters, confirmed
- `password_confirmation`: Required string, must match password

**Success Response** (201):

```json
{
    "success": true,
    "message": "Registration successful",
    "data": {
        "user": {
            "id": 2,
            "name": "John Doe",
            "email": "user@example.com",
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

**Error Response** (422):

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["This email is already registered"],
        "password": ["Password must be at least 8 characters"] // pragma: allowlist secret
    }
}
```

### 3. User Logout

**Endpoint**: `POST /api/v1/auth/logout`

**Description**: Revokes the user's access tokens and logs them out.

**Authentication**: Required (Bearer token)

**Request Headers**:

```
Authorization: Bearer {access_token}
```

**Success Response** (200):

```json
{
    "success": true,
    "message": "Logout successful",
    "data": null
}
```

### 4. Get Authenticated User

**Endpoint**: `GET /api/v1/auth/me`

**Description**: Retrieves the authenticated user's information.

**Authentication**: Required (Bearer token)

**Request Headers**:

```
Authorization: Bearer {access_token}
```

**Success Response** (200):

```json
{
    "success": true,
    "message": "User data retrieved successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com",
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    }
}
```

**Error Response** (401):

```json
{
    "message": "Unauthenticated."
}
```

## Implementation Details

### Controller Methods

#### `login(LoginRequest $request, LoginService $loginService): JsonResponse`

- **Purpose**: Handles user authentication
- **Process**:
    1. Validates request using `LoginRequest`
    2. Delegates authentication logic to `LoginService`
    3. Returns standardized success/error response
- **Error Handling**: Catches `ValidationException` and returns formatted error response

#### `signup(SignupRequest $request, SignupService $signupService): JsonResponse`

- **Purpose**: Handles user registration
- **Process**:
    1. Validates request using `SignupRequest`
    2. Delegates registration logic to `SignupService`
    3. Returns standardized success/error response with 201 status
- **Error Handling**: Catches `ValidationException` and returns formatted error response

#### `logout(Request $request, LoginService $loginService): JsonResponse`

- **Purpose**: Handles user logout
- **Process**:
    1. Retrieves authenticated user from request
    2. Delegates logout logic to `LoginService`
    3. Returns success response regardless of user state
- **Security**: Gracefully handles cases where user is null

#### `me(Request $request): JsonResponse`

- **Purpose**: Returns authenticated user data
- **Process**:
    1. Retrieves authenticated user from request
    2. Transforms user data using `UserResource`
    3. Returns standardized success response

### Security Features

1. **OAuth2 Authentication**: Uses Laravel Passport for secure token-based authentication
2. **Password Hashing**: Passwords are hashed using Laravel's Hash facade
3. **Token Expiration**: Configurable token lifetimes (15 days default, 1 week with remember_me)
4. **Input Validation**: Comprehensive validation using FormRequest classes
5. **CSRF Protection**: API routes are exempt from CSRF protection
6. **Rate Limiting**: Can be configured using Laravel's rate limiting middleware

### Error Handling

- **Validation Errors**: Returns 422 status with detailed field-specific errors
- **Authentication Errors**: Returns 401 status for invalid credentials
- **Server Errors**: Returns 500 status for unexpected errors
- **Consistent Format**: All responses follow the same structure using `ApiResponseTrait`

## Usage Examples

### cURL Examples

#### Login

```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123", // pragma: allowlist secret
    "remember_me": true
  }'
```

#### Register

```bash
curl -X POST http://localhost:8000/api/v1/auth/signup \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "user@example.com",
    "password": "password123", // pragma: allowlist secret
    "password_confirmation": "password123" // pragma: allowlist secret
  }'
```

#### Get User Info

```bash
curl -X GET http://localhost:8000/api/v1/auth/me \
  -H "Authorization: Bearer {access_token}" \
  -H "Accept: application/json"
```

#### Logout

```bash
curl -X POST http://localhost:8000/api/v1/auth/logout \
  -H "Authorization: Bearer {access_token}" \
  -H "Accept: application/json"
```

### JavaScript/Axios Examples

#### Login

```javascript
const response = await axios.post("/api/v1/auth/login", {
    email: "user@example.com",
    password: "password123", // pragma: allowlist secret
    remember_me: true,
});

const { token, user } = response.data.data;
localStorage.setItem("access_token", token.access_token);
```

#### Register

```javascript
const response = await axios.post("/api/v1/auth/signup", {
    name: "John Doe",
    email: "user@example.com",
    password: "password123", // pragma: allowlist secret
    password_confirmation: "password123", // pragma: allowlist secret
});

const { token, user } = response.data.data;
localStorage.setItem("access_token", token.access_token);
```

#### Authenticated Requests

```javascript
// Set default authorization header
axios.defaults.headers.common["Authorization"] =
    `Bearer ${localStorage.getItem("access_token")}`;

// Get user info
const userResponse = await axios.get("/api/v1/auth/me");
const user = userResponse.data.data;

// Logout
await axios.post("/api/v1/auth/logout");
localStorage.removeItem("access_token");
```

## Testing

The AuthController is thoroughly tested with feature and unit tests:

- **Feature Tests**: `tests/Feature/AuthControllerTest.php`
- **Unit Tests**: Various service and request tests
- **Test Coverage**: Login, signup, logout, and user retrieval scenarios
- **Error Testing**: Invalid credentials, validation errors, and edge cases

## Related Documentation

- [LoginService Documentation](./LoginService.md)
- [SignupService Documentation](./SignupService.md)
- [Authentication Requests Documentation](./Requests.md)
- [Laravel Passport Setup](../../general/passport-setup.md)
- [API Response Trait](../../general/api-response-trait.md)

## Configuration

### Environment Variables

```env
# Authentication Guard
AUTH_GUARD=api

# Passport Configuration
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=1
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=your-secret-key
```

### Token Lifetimes

Configured in `app/Providers/AuthServiceProvider.php`:

```php
// Token lifetimes
Passport::tokensExpireIn(now()->addDays(15));
Passport::refreshTokensExpireIn(now()->addDays(30));
Passport::personalAccessTokensExpireIn(now()->addMonths(6));
```

## Best Practices

1. **Always use HTTPS** in production for secure token transmission
2. **Store tokens securely** on the client side (avoid localStorage for sensitive apps)
3. **Implement token refresh** for long-running applications
4. **Handle token expiration** gracefully in client applications
5. **Use rate limiting** to prevent brute force attacks
6. **Validate all inputs** using FormRequest classes
7. **Log authentication events** for security monitoring
8. **Implement proper CORS** configuration for web applications

## Troubleshooting

### Common Issues

1. **"Unauthenticated" Error**
    - Ensure Bearer token is included in Authorization header
    - Check token expiration
    - Verify Passport configuration

2. **"The provided credentials are incorrect"**
    - Verify email and password
    - Check user exists in database
    - Ensure password is properly hashed

3. **Validation Errors**
    - Check request format matches expected structure
    - Verify all required fields are provided
    - Ensure email uniqueness for registration

4. **Token Generation Issues**
    - Run `php artisan passport:keys` to generate encryption keys
    - Ensure Passport client exists
    - Check database migrations are up to date

### Debug Commands

```bash
# Check Passport status
php artisan passport:client --personal

# Clear application cache
php artisan cache:clear
php artisan config:clear

# Check routes
php artisan route:list --path=auth
```
