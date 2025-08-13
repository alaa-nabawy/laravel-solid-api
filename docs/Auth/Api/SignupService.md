# SignupService Documentation

## Overview

The `SignupService` is responsible for handling user registration logic in the Laravel API SOLID application. It implements the business logic for creating new user accounts, following the Service-Repository pattern for clean separation of concerns.

**Location**: `app/Services/Auth/Api/SignupService.php`

## Purpose

The SignupService encapsulates all user registration-related business logic, including:

- User account creation and validation
- Password hashing and security
- OAuth2 token generation for new users
- Email uniqueness verification
- Integration with Laravel Passport

## Dependencies

- **Repository**: `UserRepository` - For user data access and creation
- **Facades**: `Hash` - For password hashing
- **Resources**: `UserResource` - For user data transformation
- **Exceptions**: `ValidationException` - For registration errors
- **Authentication**: Laravel Passport OAuth2 tokens

## Class Structure

### Constructor

```php
public function __construct(UserRepository $userRepository)
```

**Purpose**: Injects the UserRepository dependency for user data access and creation.

**Parameters**:

- `$userRepository`: Instance of UserRepository for database operations

## Methods

### signup(array $userData): array

**Purpose**: Registers a new user and generates an access token.

**Parameters**:

- `$userData` (array): User registration data
    - `name` (string): User's full name
    - `email` (string): User's email address
    - `password` (string): User's password (will be hashed)

**Return Value**: Array containing user information and access token

**Throws**: `ValidationException` if email already exists or other validation errors

#### Process Flow

1. **Email Uniqueness Check**: Verifies email doesn't already exist
2. **User Creation**: Creates new user with hashed password
3. **Token Generation**: Creates personal access token using Passport
4. **Response Formatting**: Returns structured response with user and token data

#### Implementation Details

```php
public function signup(array $userData): array
{
    // Check if user already exists
    $existingUser = $this->userRepository->findByField('email', $userData['email'])->first();

    if ($existingUser) {
        throw ValidationException::withMessages([
            'email' => ['The email has already been taken.'],
        ]);
    }

    // Create new user
    $user = $this->userRepository->create([
        'name'     => $userData['name'],
        'email'    => $userData['email'],
        'password' => Hash::make($userData['password']),  // pragma: allowlist secret
    ]);

    // Create access token
    $tokenResult = $user->createToken('Personal Access Token');
    $token       = $tokenResult->token;
    $token->save();

    return [
        'user'  => new UserResource($user),
        'token' => [
            'access_token' => $tokenResult->accessToken,
            'token_type'   => 'Bearer',
            'expires_at'   => $token->expires_at->toDateTimeString(),
        ],
    ];
}
```

#### Response Structure

```json
{
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
```

#### Error Handling

**Email Already Exists**:

```php
throw ValidationException::withMessages([
    'email' => ['The email has already been taken.'],
]);
```

**Security Considerations**:

- Password is hashed using Laravel's Hash facade
- Email uniqueness is verified before creation
- Secure token generation using Laravel Passport

### validateSignupData(array $userData): array

**Purpose**: Returns validation rules for user registration data.

**Parameters**:

- `$userData` (array): User data to validate (currently unused but available for future enhancements)

**Return Value**: Array of validation rules

#### Implementation Details

```php
public function validateSignupData(array $userData): array
{
    $rules = [
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed', // pragma: allowlist secret
    ];

    return $rules;
}
```

#### Validation Rules

- **name**: Required string, maximum 255 characters
- **email**: Required, valid email format, maximum 255 characters, unique in users table
- **password**: Required string, minimum 8 characters, must be confirmed

**Note**: This method provides validation rules that can be used for dynamic validation or testing purposes. The actual validation is typically handled by `SignupRequest`.

## Security Features

### Password Security

1. **Secure Hashing**: Uses Laravel's Hash facade with bcrypt
2. **Minimum Length**: 8 character minimum requirement
3. **Confirmation Required**: Password must be confirmed during registration

```php
'password' => Hash::make($userData['password'])
```

### Email Security

1. **Uniqueness Validation**: Prevents duplicate email addresses
2. **Format Validation**: Ensures valid email format
3. **Case Sensitivity**: Email comparison is case-insensitive

### Token Security

1. **Immediate Token Generation**: User gets access token upon registration
2. **Secure Token Format**: Uses Laravel Passport's secure token generation
3. **Configurable Expiration**: Token lifetime configured in AuthServiceProvider

## Integration with Repository Pattern

### UserRepository Usage

```php
// Check existing user
$existingUser = $this->userRepository->findByField('email', $userData['email'])->first();

// Create new user
$user = $this->userRepository->create([
    'name'     => $userData['name'],
    'email'    => $userData['email'],
    'password' => Hash::make($userData['password']),  // pragma: allowlist secret
]);
```

**Benefits**:

- **Abstraction**: Service doesn't directly interact with Eloquent models
- **Testability**: Easy to mock repository for unit testing
- **Consistency**: Uses same data access patterns across application
- **Flexibility**: Repository can be swapped without changing service logic

## Error Handling Strategy

### ValidationException Usage

```php
throw ValidationException::withMessages([
    'email' => ['The email has already been taken.'],
]);
```

**Advantages**:

- **Laravel Integration**: Automatically handled by Laravel's exception handler
- **Consistent Format**: Matches Laravel's validation error format
- **HTTP Status**: Automatically returns 422 status code
- **Field-Specific**: Errors are associated with specific form fields

### Duplicate Email Handling

**Manual Check vs Database Constraint**:

- Service performs manual check for better error messaging
- Database unique constraint provides backup protection
- Allows for custom error messages and handling

## Testing

### Unit Test Examples

```php
// Test successful registration
public function test_successful_signup()
{
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123' // pragma: allowlist secret
    ];

    $result = $this->signupService->signup($userData);

    $this->assertArrayHasKey('user', $result);
    $this->assertArrayHasKey('token', $result);
    $this->assertEquals('John Doe', $result['user']->name);
    $this->assertEquals('Bearer', $result['token']['token_type']);
}

// Test duplicate email
public function test_duplicate_email_throws_exception()
{
    User::factory()->create(['email' => 'existing@example.com']);

    $this->expectException(ValidationException::class);

    $userData = [
        'name' => 'Jane Doe',
        'email' => 'existing@example.com',
        'password' => 'password123' // pragma: allowlist secret
    ];

    $this->signupService->signup($userData);
}

// Test password hashing
public function test_password_is_hashed()
{
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'plaintext-password' // pragma: allowlist secret
    ];

    $result = $this->signupService->signup($userData);
    $user = User::find($result['user']->id);

    $this->assertTrue(Hash::check('plaintext-password', $user->password));
    $this->assertNotEquals('plaintext-password', $user->password);
}

// Test validation rules
public function test_validation_rules()
{
    $rules = $this->signupService->validateSignupData([]);

    $this->assertArrayHasKey('name', $rules);
    $this->assertArrayHasKey('email', $rules);
    $this->assertArrayHasKey('password', $rules);
    $this->assertStringContainsString('required', $rules['name']);
    $this->assertStringContainsString('unique:users', $rules['email']);
    $this->assertStringContainsString('min:8', $rules['password']);
}
```

### Mock Repository Testing

```php
public function test_signup_with_mock_repository()
{
    $mockRepository = Mockery::mock(UserRepository::class);

    // Mock findByField to return empty collection (no existing user)
    $mockRepository->shouldReceive('findByField')
        ->with('email', 'john@example.com')
        ->andReturn(collect([]));

    // Mock create to return new user
    $user = User::factory()->make([
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);

    $mockRepository->shouldReceive('create')
        ->andReturn($user);

    $signupService = new SignupService($mockRepository);

    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123' // pragma: allowlist secret
    ];

    $result = $signupService->signup($userData);

    $this->assertInstanceOf(UserResource::class, $result['user']);
    $this->assertArrayHasKey('token', $result);
}
```

## Performance Considerations

### Database Operations

1. **Email Lookup**: Single query to check existing email
2. **User Creation**: Single insert operation
3. **Token Generation**: Handled efficiently by Passport

### Optimization Strategies

1. **Database Indexing**: Ensure email field is indexed
2. **Repository Caching**: Can implement caching for frequently accessed data
3. **Batch Operations**: For bulk user creation scenarios

### Scalability Considerations

1. **Unique Constraint**: Database-level unique constraint on email
2. **Transaction Safety**: Consider wrapping in database transaction
3. **Rate Limiting**: Implement at controller/middleware level

## Configuration

### Model Configuration

```php
// User model should have fillable fields
protected $fillable = [
    'name',
    'email',
    'password',  // pragma: allowlist secret
];

// Hidden fields for API responses
protected $hidden = [
    'password', // pragma: allowlist secret
    'remember_token',
];

// Password hashing
protected $casts = [
    'email_verified_at' => 'datetime',
    'password' => 'hashed', // pragma: allowlist secret
];
```

### Validation Configuration

```php
// Custom validation rules can be added
'password' => [
    'required',
    'string',
    'min:8',
    'confirmed',
    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).*$/' // Example: require mixed case and numbers
]
```

## Usage Examples

### Basic Usage in Controller

```php
public function signup(SignupRequest $request, SignupService $signupService)
{
    try {
        $result = $signupService->signup($request->validated());
        return $this->createdResponse($result, 'Registration successful');
    } catch (ValidationException $e) {
        return $this->validationErrorResponse($e);
    }
}
```

### Service Injection

```php
// Automatic dependency injection
public function __construct(SignupService $signupService)
{
    $this->signupService = $signupService;
}

// Manual resolution
$signupService = app(SignupService::class);
```

### Custom Validation Usage

```php
// Get validation rules for custom validation
$rules = $signupService->validateSignupData($userData);
$validator = Validator::make($userData, $rules);

if ($validator->fails()) {
    // Handle validation errors
}
```

## Integration with Form Requests

The SignupService works seamlessly with `SignupRequest`:

```php
// SignupRequest validation rules should match service expectations
public function rules(): array
{
    return [
        'name'                  => 'required|string|max:255',
        'email'                 => 'required|email|unique:users,email',
        'password'              => 'required|string|min:8|confirmed', // pragma: allowlist secret
        'password_confirmation' => 'required|string', // pragma: allowlist secret
    ];
}
```

## Related Documentation

- [AuthController Documentation](./AuthController.md)
- [LoginService Documentation](./LoginService.md)
- [SignupRequest Documentation](./Requests.md)
- [UserRepository Documentation](../../Repositories/UserRepository.md)
- [Service-Repository Pattern](../../general/service-repository-pattern.md)
- [Laravel Passport Setup](../../general/passport-setup.md)

## Best Practices

1. **Single Responsibility**: Service only handles user registration logic
2. **Dependency Injection**: Uses constructor injection for dependencies
3. **Exception Handling**: Throws appropriate exceptions for error cases
4. **Security First**: Implements secure registration practices
5. **Password Security**: Always hash passwords before storage
6. **Email Validation**: Verify email uniqueness and format
7. **Testability**: Designed for easy unit testing
8. **Documentation**: Comprehensive PHPDoc comments
9. **Type Hints**: Strong typing for better code quality
10. **Return Types**: Explicit return type declarations

## Troubleshooting

### Common Issues

1. **"The email has already been taken"**
    - Check if user already exists in database
    - Verify email case sensitivity handling
    - Ensure proper unique constraint on email field

2. **Token Generation Fails**
    - Run `php artisan passport:keys`
    - Check Passport client configuration
    - Verify database migrations are up to date

3. **Password Hashing Issues**
    - Ensure Hash facade is properly imported
    - Check bcrypt configuration
    - Verify password field length in database

4. **Validation Errors**
    - Check SignupRequest validation rules
    - Verify database field constraints
    - Ensure proper error message formatting

### Debug Commands

```bash
# Check if email exists
php artisan tinker
>>> App\Models\User::where('email', 'user@example.com')->exists()

# Test password hashing
>>> Hash::make('password123')
>>> Hash::check('password123', '$2y$10$...')

# Check Passport configuration
php artisan passport:client --personal

# Verify database structure
php artisan migrate:status
php artisan db:show --table=users
```

## Future Enhancements

1. **Email Verification**: Add email verification workflow
2. **Social Registration**: Integrate with social media providers
3. **Profile Pictures**: Add avatar upload functionality
4. **User Roles**: Implement role-based registration
5. **Custom Fields**: Support for additional user fields
6. **Registration Analytics**: Track registration metrics
7. **Invitation System**: Support for invitation-based registration
8. **Multi-step Registration**: Break registration into multiple steps
