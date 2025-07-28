# Service Repository Pattern Documentation

## 🏗️ Overview

The Laravel API SOLID project implements a robust Service Repository pattern that follows SOLID principles and provides a clean separation between business logic and data access layers.

## 📐 Pattern Architecture

### Repository Layer

The repository layer abstracts data access logic and provides a uniform interface for data operations.

**Key Features:**

- Uses **Prettus L5 Repository** package (v2.10)
- Implements `BaseRepository` for common operations
- Provides search, filtering, and pagination capabilities
- Supports criteria-based queries
- Enables easy testing through mocking

### Service Layer

The service layer encapsulates business logic and orchestrates operations between multiple repositories.

**Key Features:**

- **CRUD Separation**: Individual services for Create, Read, Update, Delete operations
- **Constructor Property Promotion**: Modern PHP 8.0+ syntax
- **Dependency Injection**: Clean dependency management
- **Business Logic Encapsulation**: Keeps controllers thin

## 🚀 CRUD Generation System

The project includes powerful Artisan commands for rapid development with clean architecture patterns.

### `make:structure` Command

Generate complete CRUD structures with repositories, services, and optional resource folders.

#### Basic Usage

```bash
# Generate full CRUD structure
php artisan make:structure Post
```

#### Advanced Options

```bash
# Generate specific CRUD methods only
php artisan make:structure Post --only=create,read,update

# Skip resource folder generation
php artisan make:structure Post --no-resource

# Combine options
php artisan make:structure Post --only=read,update --no-resource
```

#### Generated Structure

```
app/
├── Http/
│   └── Resources/
│       └── Post/
│           ├── Admin/
│           └── Api/
├── Services/
│   └── Post/
│       ├── PostCreateService.php
│       ├── PostReadService.php
│       ├── PostUpdateService.php
│       └── PostDeleteService.php
└── Repositories/
    └── PostRepository.php
```

### Command Features

- **🎯 Selective Generation**: Choose specific CRUD methods to generate
- **📁 Optional Resources**: Skip resource folder creation when not needed
- **🔧 Modern PHP**: Constructor property promotion (PHP 8.0+)
- **📝 Clean Code**: Consistent naming conventions and structure
- **⚡ Rapid Development**: Generate complete CRUD structures in seconds

## 📁 File Templates

### Repository Template

```php
<?php

namespace App\Repositories;

use App\Models\{{model}};
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class {{model}}Repository extends BaseRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return {{model}}::class;
    }

    /**
     * Boot up the repository, pushing criteria
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
```

### Service Template

```php
<?php

namespace App\Services\{{model}};

use App\Repositories\{{model}}Repository;

class {{className}}Service
{
    public function __construct(private {{model}}Repository ${{lowerModel}}Repository) {}

    public function {{lowerMethod}}(array $data)
    {
        // Your {{lowerMethod}} logic goes here
    }
}
```

## 🔧 Repository Configuration

The repository pattern is configured in `config/repository.php` with the following capabilities:

### Search Parameters

- **search**: General search across specified fields
- **searchFields**: Define which fields to search
- **filter**: Specify fields to return in response
- **orderBy**: Sort results by field
- **sortedBy**: Sort direction (asc/desc)
- **searchJoin**: Search method (AND/OR)

### Example Usage

```
# Search with field specification
GET /api/posts?search=laravel&searchFields=title:like,content:like

# Filter response fields
GET /api/posts?filter=id,title,created_at

# Order and sort
GET /api/posts?orderBy=created_at&sortedBy=desc

# Combined search with AND logic
GET /api/posts?search=laravel&searchFields=title:like&searchJoin=and
```

## 💡 Implementation Examples

### Repository Implementation

```php
<?php

namespace App\Repositories;

use App\Models\User;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class UserRepository extends BaseRepository
{
    public function model()
    {
        return User::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    // Custom repository methods
    public function findByEmail(string $email)
    {
        return $this->findByField('email', $email)->first();
    }

    public function getActiveUsers()
    {
        return $this->findByField('status', 'active');
    }
}
```

### Service Implementation

```php
<?php

namespace App\Services\User;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class UserCreateService
{
    public function __construct(private UserRepository $userRepository) {}

    public function create(array $data)
    {
        // Business logic validation
        $this->validateUserData($data);

        // Hash password
        $data['password'] = Hash::make($data['password']);

        // Create user through repository
        return $this->userRepository->create($data);
    }

    private function validateUserData(array $data): void
    {
        // Custom business validation logic
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new \Exception('Email already exists');
        }
    }
}
```

### Controller Integration

```php
<?php

namespace App\Http\Controllers;

use App\Services\User\UserCreateService;
use App\Services\User\UserReadService;
use App\Http\Requests\CreateUserRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private UserCreateService $createService,
        private UserReadService $readService
    ) {}

    public function store(CreateUserRequest $request): JsonResponse
    {
        $user = $this->createService->create($request->validated());

        return response()->json([
            'message' => 'User created successfully',
            'data' => $user
        ], 201);
    }

    public function index(): JsonResponse
    {
        $users = $this->readService->getAllUsers();

        return response()->json([
            'data' => $users
        ]);
    }
}
```

## 🧪 Testing Strategy

### Repository Testing

```php
class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user()
    {
        $repository = app(UserRepository::class);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $user = $repository->create($userData);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }
}
```

### Service Testing

```php
class UserCreateServiceTest extends TestCase
{
    public function test_creates_user_with_hashed_password()
    {
        $mockRepository = Mockery::mock(UserRepository::class);
        $service = new UserCreateService($mockRepository);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $mockRepository->shouldReceive('findByEmail')
            ->once()
            ->andReturn(null);

        $mockRepository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($data) {
                return Hash::check('password123', $data['password']);
            }))
            ->andReturn(new User($userData));

        $result = $service->create($userData);

        $this->assertInstanceOf(User::class, $result);
    }
}
```

## 📊 Benefits

### Code Organization

- **Separation of Concerns**: Clear boundaries between layers
- **Single Responsibility**: Each class has one specific purpose
- **Dependency Inversion**: High-level modules don't depend on low-level modules

### Development Speed

- **Rapid Generation**: Complete CRUD structures in seconds
- **Consistent Structure**: Standardized code organization
- **Reduced Boilerplate**: Automated code generation

### Maintainability

- **Easy Testing**: Mockable dependencies
- **Flexible Implementation**: Easy to swap implementations
- **Clean Controllers**: Thin controllers with delegated logic

### Scalability

- **Reusable Services**: Services can be used across different controllers
- **Cacheable Repositories**: Built-in caching support
- **Query Optimization**: Criteria-based query building

## 🔮 Advanced Features

### Repository Criteria

```php
// Custom criteria for complex queries
class ActiveUsersCriteria implements CriteriaInterface
{
    public function apply($model, RepositoryInterface $repository)
    {
        return $model->where('status', 'active')
                    ->where('email_verified_at', '!=', null);
    }
}

// Usage in repository
$users = $this->userRepository
    ->pushCriteria(new ActiveUsersCriteria())
    ->all();
```

### Service Composition

```php
class UserRegistrationService
{
    public function __construct(
        private UserCreateService $createService,
        private EmailVerificationService $emailService,
        private WelcomeEmailService $welcomeService
    ) {}

    public function register(array $data)
    {
        $user = $this->createService->create($data);
        $this->emailService->sendVerification($user);
        $this->welcomeService->sendWelcomeEmail($user);

        return $user;
    }
}
```

## 📚 Best Practices

1. **Keep Services Focused**: One service per business operation
2. **Use Type Hints**: Leverage PHP's type system
3. **Handle Exceptions**: Proper error handling in services
4. **Validate Input**: Business logic validation in services
5. **Test Thoroughly**: Unit and integration tests
6. **Document APIs**: Clear method documentation
7. **Use Transactions**: Wrap complex operations in database transactions
8. **Cache Wisely**: Implement caching at the repository level
