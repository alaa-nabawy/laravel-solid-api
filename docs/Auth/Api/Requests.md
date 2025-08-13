# Authentication Requests Documentation

## Overview

The authentication requests handle validation for user authentication operations in the Laravel API SOLID application. They implement Laravel's FormRequest pattern to provide robust input validation, custom error messages, and authorization logic.

**Locations**:

- `app/Http/Requests/Auth/Api/LoginRequest.php`
- `app/Http/Requests/Auth/Api/SignupRequest.php`

## Purpose

Authentication requests serve multiple purposes:

- **Input Validation**: Ensure all required fields are present and valid
- **Data Sanitization**: Clean and format input data
- **Error Messaging**: Provide user-friendly validation error messages
- **Authorization**: Control access to authentication endpoints
- **Type Safety**: Guarantee data types for service layer consumption

## LoginRequest

### Overview

Handles validation for user login operations, ensuring email and password are provided in the correct format.

### Class Structure

```php
namespace App\Http\Requests\Auth\Api;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    public function rules(): array
    public function messages(): array
}
```

### Methods

#### authorize(): bool

**Purpose**: Determines if the user is authorized to make this request.

**Return Value**: Always returns `true` as login is a public endpoint

```php
public function authorize(): bool
{
    return true;
}
```

**Security Note**: Login requests are always authorized since they're public endpoints. Authorization happens after successful authentication.

#### rules(): array

**Purpose**: Defines validation rules for login data.

**Return Value**: Array of validation rules

```php
public function rules(): array
{
    return [
        'email'    => 'required|email',
        'password' => 'required|string', // pragma: allowlist secret
    ];
}
```

**Validation Rules**:

- **email**: Required field, must be valid email format
- **password**: Required field, must be string type
- **remember_me**: Optional boolean (handled by service layer)

#### messages(): array

**Purpose**: Provides custom error messages for validation failures.

**Return Value**: Array of custom error messages

```php
public function messages(): array
{
    return [
        'email.required'    => 'Email is required',
        'email.email'       => 'Please provide a valid email address',
        'password.required' => 'Password is required',
    ];
}
```

### Usage Example

```php
// In AuthController
public function login(LoginRequest $request, LoginService $loginService)
{
    // $request->validated() returns validated data
    $credentials = $request->validated();

    // Additional fields can be accessed
    $rememberMe = $request->input('remember_me', false);

    $result = $loginService->login(array_merge($credentials, [
        'remember_me' => $rememberMe
    ]));

    return $this->successResponse($result, 'Login successful');
}
```

### Request Format

```json
{
    "email": "user@example.com",
    "password": "password123", // pragma: allowlist secret
    "remember_me": true
}
```

### Validation Errors

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["Email is required"],
        "password": ["Password is required"]
    }
}
```

## SignupRequest

### Overview

Handles validation for user registration operations, ensuring all required fields are present, properly formatted, and meet security requirements.

### Class Structure

```php
namespace App\Http\Requests\Auth\Api;

use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
{
    public function authorize(): bool
    public function rules(): array
    public function messages(): array
}
```

### Methods

#### authorize(): bool

**Purpose**: Determines if the user is authorized to make this request.

**Return Value**: Always returns `true` as registration is a public endpoint

```php
public function authorize(): bool
{
    return true;
}
```

#### rules(): array

**Purpose**: Defines comprehensive validation rules for user registration.

**Return Value**: Array of validation rules

```php
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

**Validation Rules**:

- **name**: Required string, maximum 255 characters
- **email**: Required, valid email format, unique in users table
- **password**: Required string, minimum 8 characters, must be confirmed
- **password_confirmation**: Required string, must match password

#### messages(): array

**Purpose**: Provides detailed custom error messages for all validation scenarios.

**Return Value**: Array of custom error messages

```php
public function messages(): array
{
    return [
        'name.required'                  => 'Name is required',
        'name.max'                       => 'Name cannot exceed 255 characters',
        'email.required'                 => 'Email is required',
        'email.email'                    => 'Please provide a valid email address',
        'email.unique'                   => 'This email is already registered',
        'password.required'              => 'Password is required',
        'password.min'                   => 'Password must be at least 8 characters',
        'password.confirmed'             => 'Password confirmation does not match',
        'password_confirmation.required' => 'Password confirmation is required',
    ];
}
```

### Usage Example

```php
// In AuthController
public function signup(SignupRequest $request, SignupService $signupService)
{
    // $request->validated() returns only validated fields
    $userData = $request->validated();

    // password_confirmation is automatically excluded from validated()
    // Only name, email, and password are passed to service

    $result = $signupService->signup($userData);

    return $this->createdResponse($result, 'Registration successful');
}
```

### Request Format

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123", // pragma: allowlist secret
    "password_confirmation": "password123" // pragma: allowlist secret
}
```

### Validation Errors

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "name": ["Name is required"],
        "email": ["This email is already registered"],
        "password": ["Password must be at least 8 characters"],
        "password_confirmation": ["Password confirmation does not match"]
    }
}
```

## Advanced Features

### Custom Validation Rules

You can extend the requests with custom validation rules:

```php
// In SignupRequest
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => [
            'required',
            'email',
            'unique:users,email',
            'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
        ],
        'password' => [
            'required',
            'string',
            'min:8',
            'confirmed',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/' // Strong password // pragma: allowlist secret
        ],
        'password_confirmation' => 'required|string', // pragma: allowlist secret
    ];
}
```

### Conditional Validation

```php
// Example: Different rules based on conditions
public function rules(): array
{
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed', // pragma: allowlist secret
        'password_confirmation' => 'required|string', // pragma: allowlist secret
    ];

    // Add additional rules based on conditions
    if ($this->input('is_admin')) {
        $rules['admin_code'] = 'required|string';
    }

    return $rules;
}
```

### Data Preparation

```php
// Prepare data before validation
protected function prepareForValidation()
{
    $this->merge([
        'email' => strtolower($this->email),
        'name' => ucwords(strtolower($this->name)),
    ]);
}
```

## Security Considerations

### Password Security

1. **Minimum Length**: 8 characters minimum
2. **Confirmation Required**: Prevents typos
3. **No Maximum Length**: Allows for strong passwords
4. **Hashing**: Handled by service layer, not request

```php
// Enhanced password rules
'password' => [
    'required',
    'string',
    'min:8',
    'max:255', // Reasonable maximum
    'confirmed',
    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).*$/' // Mixed case + numbers
]
```

### Email Security

1. **Format Validation**: Ensures valid email format
2. **Uniqueness Check**: Prevents duplicate accounts
3. **Case Insensitive**: Email comparison is case-insensitive

### Input Sanitization

```php
// Automatic sanitization
protected function prepareForValidation()
{
    $this->merge([
        'email' => trim(strtolower($this->email)),
        'name' => trim($this->name),
    ]);
}
```

## Testing

### Unit Tests for Validation

```php
// Test valid login request
public function test_valid_login_request()
{
    $data = [
        'email' => 'user@example.com',
        'password' => 'password123' // pragma: allowlist secret
    ];

    $request = new LoginRequest();
    $validator = Validator::make($data, $request->rules());

    $this->assertTrue($validator->passes());
}

// Test invalid email format
public function test_invalid_email_format()
{
    $data = [
        'email' => 'invalid-email',
        'password' => 'password123' // pragma: allowlist secret
    ];

    $request = new LoginRequest();
    $validator = Validator::make($data, $request->rules(), $request->messages());

    $this->assertTrue($validator->fails());
    $this->assertEquals(
        'Please provide a valid email address',
        $validator->errors()->first('email')
    );
}

// Test signup validation
public function test_signup_password_confirmation()
{
    $data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123', // pragma: allowlist secret
        'password_confirmation' => 'different-password' // pragma: allowlist secret
    ];

    $request = new SignupRequest();
    $validator = Validator::make($data, $request->rules(), $request->messages());

    $this->assertTrue($validator->fails());
    $this->assertStringContainsString(
        'Password confirmation does not match',
        $validator->errors()->first('password')
    );
}
```

### Feature Tests

```php
// Test login endpoint validation
public function test_login_requires_email_and_password()
{
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
}

// Test signup endpoint validation
public function test_signup_validates_all_fields()
{
    $response = $this->postJson('/api/v1/auth/signup', [
        'name' => '',
        'email' => 'invalid-email',
        'password' => '123', // Too short // pragma: allowlist secret
        'password_confirmation' => 'different' // pragma: allowlist secret
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'name',
            'email',
            'password'
        ]);
}
```

## Error Response Format

### Standard Laravel Validation Response

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["Error message 1", "Error message 2"]
    }
}
```

### Custom Error Response (using ApiResponseTrait)

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["Please provide a valid email address"],
        "password": ["Password is required"]
    }
}
```

## Best Practices

### 1. Comprehensive Validation

```php
// Cover all possible scenarios
public function rules(): array
{
    return [
        'email' => [
            'required',
            'string',
            'email:rfc,dns', // Strict email validation
            'max:255',
            'unique:users,email'
        ],
        'password' => [
            'required',
            'string',
            'min:8',
            'max:255',
            'confirmed'
        ]
    ];
}
```

### 2. User-Friendly Messages

```php
// Clear, actionable error messages
public function messages(): array
{
    return [
        'email.required' => 'Please enter your email address',
        'email.email' => 'Please enter a valid email address',
        'email.unique' => 'An account with this email already exists',
        'password.min' => 'Password must be at least 8 characters long',
        'password.confirmed' => 'Password confirmation does not match'
    ];
}
```

### 3. Security-First Approach

```php
// Implement strong validation rules
'password' => [
    'required',
    'string',
    'min:8',
    'confirmed',
    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/' // Strong password
]
```

### 4. Consistent Naming

```php
// Use consistent field names across requests
'email' => 'required|email',
'password' => 'required|string|min:8', // pragma: allowlist secret
'password_confirmation' => 'required|string' // pragma: allowlist secret
```

## Related Documentation

- [AuthController Documentation](./AuthController.md)
- [LoginService Documentation](./LoginService.md)
- [SignupService Documentation](./SignupService.md)
- [Laravel Validation Documentation](https://laravel.com/docs/validation)
- [FormRequest Documentation](https://laravel.com/docs/validation#form-request-validation)

## Troubleshooting

### Common Issues

1. **Validation Not Working**
    - Check request is properly type-hinted in controller
    - Verify rules() method returns array
    - Ensure FormRequest is imported

2. **Custom Messages Not Showing**
    - Check messages() method syntax
    - Verify message keys match rule names
    - Ensure proper array structure

3. **Unique Validation Failing**
    - Check database table and column names
    - Verify database connection
    - Ensure proper indexing on unique fields

4. **Password Confirmation Issues**
    - Verify field name is exactly 'password_confirmation'
    - Check both fields are present in request
    - Ensure 'confirmed' rule is applied to 'password' field

### Debug Commands

```bash
# Test validation rules
php artisan tinker
>>> $request = new App\Http\Requests\Auth\Api\LoginRequest();
>>> $request->rules()

# Check validation messages
>>> $request->messages()

# Test validator directly
>>> $validator = Validator::make(['email' => 'invalid'], $request->rules());
>>> $validator->fails()
>>> $validator->errors()->toArray()
```
