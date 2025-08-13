# LoginService Documentation

## Overview

The `LoginService` is responsible for handling user authentication logic in the Laravel API SOLID application. It implements the business logic for user login and logout operations, following the Service-Repository pattern for clean separation of concerns.

**Location**: `app/Services/Auth/Api/LoginService.php`

## Purpose

The LoginService encapsulates all authentication-related business logic, including:

- User credential validation
- OAuth2 token generation and management
- User logout and token revocation
- Integration with Laravel Passport

## Dependencies

- **Repository**: `UserRepository` - For user data access
- **Facades**: `Hash` - For password verification
- **Resources**: `UserResource` - For user data transformation
- **Exceptions**: `ValidationException` - For authentication errors
- **Authentication**: Laravel Passport OAuth2 tokens

## Class Structure

### Constructor

```php
public function __construct(UserRepository $userRepository)
```

**Purpose**: Injects the UserRepository dependency for user data access.

**Parameters**:

- `$userRepository`: Instance of UserRepository for database operations

## Methods

### login(array $credentials): array

**Purpose**: Authenticates a user and generates an access token.

**Parameters**:

- `$credentials` (array): User login credentials
    - `email` (string): User's email address
    - `password` (string): User's password
    - `remember_me` (bool, optional): Extends token expiration if true

**Return Value**: Array containing token and user information

**Throws**: `ValidationException` if credentials are invalid

#### Process Flow

1. **User Lookup**: Searches for user by email using UserRepository
2. **Password Verification**: Uses Hash::check() to verify password
3. **Token Generation**: Creates personal access token using Passport
4. **Token Configuration**: Sets expiration based on remember_me option
5. **Response Formatting**: Returns structured response with token and user data

#### Implementation Details

```php
public function login(array $credentials): array
{
    // Find user by email
    $user = $this->userRepository->findByField('email', $credentials['email'])->first();

    // Validate credentials
    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    // Generate access token
    $tokenResult = $user->createToken('Personal Access Token');
    $token = $tokenResult->token;

    // Set token expiration
    if (isset($credentials['remember_me']) && $credentials['remember_me']) {
        $token->expires_at = now()->addWeeks(1);
    }

    $token->save();

    // Return formatted response
    return [
        'token' => [
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $token->expires_at->toDateTimeString(),
        ],
        'user' => new UserResource($user),
    ];
}
```

#### Response Structure

```json
{
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
```

#### Error Handling

**Invalid Credentials**:

```php
throw ValidationException::withMessages([
    'email' => ['The provided credentials are incorrect.'],
]);
```

**Security Considerations**:

- Generic error message prevents user enumeration
- Password verification uses secure Hash::check() method
- No sensitive information exposed in error responses

### logout(User $user): bool

**Purpose**: Revokes all access tokens for the specified user.

**Parameters**:

- `$user` (User): The authenticated user instance

**Return Value**: Boolean indicating success (always true)

#### Implementation Details

```php
public function logout(User $user): bool
{
    $user->tokens()->delete();
    return true;
}
```

#### Process Flow

1. **Token Revocation**: Deletes all tokens associated with the user
2. **Success Response**: Returns true to indicate successful logout

#### Security Features

- **Complete Token Revocation**: Removes all user tokens, not just current session
- **Immediate Effect**: Tokens are invalidated immediately
- **No Partial Logout**: Ensures complete security by revoking all access

## Token Management

### Token Types

**Personal Access Tokens**: Used for API authentication

- Generated using Laravel Passport
- Bearer token format
- Configurable expiration times

### Token Expiration

**Default Expiration**: Configured in AuthServiceProvider

```php
Passport::tokensExpireIn(now()->addDays(15));
```

**Remember Me Expiration**: Extended to 1 week

```php
if (isset($credentials['remember_me']) && $credentials['remember_me']) {
    $token->expires_at = now()->addWeeks(1);
}
```

### Token Security

1. **Secure Generation**: Uses Laravel Passport's secure token generation
2. **Proper Expiration**: Tokens have defined lifetimes
3. **Complete Revocation**: Logout removes all user tokens
4. **Bearer Format**: Standard OAuth2 bearer token format

## Integration with Repository Pattern

### UserRepository Usage

```php
// Find user by email
$user = $this->userRepository->findByField('email', $credentials['email'])->first();
```

**Benefits**:

- **Abstraction**: Service doesn't directly interact with Eloquent models
- **Testability**: Easy to mock repository for unit testing
- **Flexibility**: Repository can be swapped without changing service logic
- **Consistency**: Uses same data access patterns across application

## Error Handling Strategy

### ValidationException Usage

```php
throw ValidationException::withMessages([
    'email' => ['The provided credentials are incorrect.'],
]);
```

**Advantages**:

- **Laravel Integration**: Automatically handled by Laravel's exception handler
- **Consistent Format**: Matches Laravel's validation error format
- **HTTP Status**: Automatically returns 422 status code
- **Field-Specific**: Errors are associated with specific form fields

### Security Best Practices

1. **Generic Error Messages**: Prevents user enumeration attacks
2. **No Information Leakage**: Doesn't reveal whether email exists
3. **Consistent Response Time**: Same processing time for valid/invalid emails
4. **Secure Password Verification**: Uses Laravel's Hash facade

## Testing

### Unit Test Examples

```php
// Test successful login
public function test_successful_login()
{
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $credentials = [
        'email' => 'test@example.com',
        'password' => 'password123' // pragma: allowlist secret
    ];

    $result = $this->loginService->login($credentials);

    $this->assertArrayHasKey('token', $result);
    $this->assertArrayHasKey('user', $result);
    $this->assertEquals('Bearer', $result['token']['token_type']);
}

// Test invalid credentials
public function test_invalid_credentials()
{
    $this->expectException(ValidationException::class);

    $credentials = [
        'email' => 'nonexistent@example.com',
        'password' => 'wrongpassword' // pragma: allowlist secret
    ];

    $this->loginService->login($credentials);
}

// Test logout functionality
public function test_logout()
{
    $user = User::factory()->create();
    $user->createToken('Test Token');

    $this->assertTrue($this->loginService->logout($user));
    $this->assertEquals(0, $user->tokens()->count());
}
```

### Mock Repository Testing

```php
public function test_login_with_mock_repository()
{
    $mockRepository = Mockery::mock(UserRepository::class);
    $user = User::factory()->make([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $mockRepository->shouldReceive('findByField')
        ->with('email', 'test@example.com')
        ->andReturn(collect([$user]));

    $loginService = new LoginService($mockRepository);
    // ... test implementation
}
```

## Performance Considerations

### Database Queries

1. **Single User Lookup**: Only one database query per login attempt
2. **Indexed Email Field**: Email field should be indexed for fast lookups
3. **Efficient Token Storage**: Passport handles token storage efficiently

### Optimization Tips

1. **Repository Caching**: Can implement caching in UserRepository
2. **Token Cleanup**: Regular cleanup of expired tokens
3. **Rate Limiting**: Implement at controller/middleware level

## Configuration

### Environment Variables

```env
# Passport Configuration
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=1
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=your-secret-key
```

### Service Provider Configuration

```php
// app/Providers/AuthServiceProvider.php
Passport::tokensExpireIn(now()->addDays(15));
Passport::refreshTokensExpireIn(now()->addDays(30));
Passport::personalAccessTokensExpireIn(now()->addMonths(6));
```

## Usage Examples

### Basic Usage in Controller

```php
public function login(LoginRequest $request, LoginService $loginService)
{
    try {
        $result = $loginService->login($request->validated());
        return $this->successResponse($result, 'Login successful');
    } catch (ValidationException $e) {
        return $this->validationErrorResponse($e);
    }
}
```

### Service Injection

```php
// Automatic dependency injection
public function __construct(LoginService $loginService)
{
    $this->loginService = $loginService;
}

// Manual resolution
$loginService = app(LoginService::class);
```

## Related Documentation

- [AuthController Documentation](./AuthController.md)
- [SignupService Documentation](./SignupService.md)
- [UserRepository Documentation](../../Repositories/UserRepository.md)
- [Service-Repository Pattern](../../general/service-repository-pattern.md)
- [Laravel Passport Setup](../../general/passport-setup.md)

## Best Practices

1. **Single Responsibility**: Service only handles authentication logic
2. **Dependency Injection**: Uses constructor injection for dependencies
3. **Exception Handling**: Throws appropriate exceptions for error cases
4. **Security First**: Implements secure authentication practices
5. **Testability**: Designed for easy unit testing
6. **Documentation**: Comprehensive PHPDoc comments
7. **Type Hints**: Strong typing for better code quality
8. **Return Types**: Explicit return type declarations

## Troubleshooting

### Common Issues

1. **"The provided credentials are incorrect"**
    - Check user exists in database
    - Verify password is properly hashed
    - Ensure email case sensitivity

2. **Token Generation Fails**
    - Run `php artisan passport:keys`
    - Check Passport client configuration
    - Verify database migrations

3. **Logout Not Working**
    - Check user is properly authenticated
    - Verify token exists in database
    - Ensure proper user instance passed

### Debug Commands

```bash
# Check user exists
php artisan tinker
>>> App\Models\User::where('email', 'user@example.com')->first()

# Check tokens
>>> $user->tokens()->get()

# Verify password hash
>>> Hash::check('password', $user->password)
```
