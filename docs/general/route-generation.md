# Route Generation Documentation

## 🚀 Enhanced Route Generation Commands

The Laravel API SOLID project includes powerful Artisan commands for rapid development with clean architecture patterns and automated route management.

## 📋 Available Commands

### 1. `make:structure` - Enhanced CRUD Structure Generator

Generate complete CRUD structures with repositories, services, and optional resource folders.

#### Command Signature
```bash
php artisan make:structure {model} {--no-resource} {--only=}
```

#### Basic Usage
```bash
# Generate full CRUD structure
php artisan make:structure Post
```

#### Advanced Options

**Selective CRUD Generation**
```bash
# Generate specific CRUD methods only
php artisan make:structure Post --only=create,read,update
php artisan make:structure Post --only=read,update
php artisan make:structure Post --only=create,delete
```

**Skip Resource Folders**
```bash
# Skip resource folder generation
php artisan make:structure Post --no-resource
```

**Combined Options**
```bash
# Combine options for maximum flexibility
php artisan make:structure Post --only=read,update --no-resource
```

#### Generated Structure
```
app/
├── Http/
│   └── Resources/
│       └── Post/
│           ├── Admin/          # Admin-specific resources
│           └── Api/            # API-specific resources
├── Services/
│   └── Post/
│       ├── PostCreateService.php
│       ├── PostReadService.php
│       ├── PostUpdateService.php
│       └── PostDeleteService.php
└── Repositories/
    └── PostRepository.php
```

### 2. `make:route-group` - Route Group Generator

Create route groups with middleware and automatic registration in the application bootstrap.

#### Command Signature
```bash
php artisan make:route-group {name} {--prefix=} {--middleware=}
```

#### Basic Usage
```bash
# Generate route group with default settings
php artisan make:route-group Admin
```

#### Advanced Options
```bash
# Custom prefix and middleware
php artisan make:route-group Admin --prefix=admin --middleware=admin

# API route group
php artisan make:route-group Api --prefix=api/v1 --middleware=api

# Public route group
php artisan make:route-group Public --prefix=public --middleware=web
```

#### What Gets Generated

1. **Route File**: `routes/{name}.php`
2. **Middleware Registration**: Added to application configuration
3. **Route Group Registration**: Automatically registered in `bootstrap/app.php`

### 3. `generate:crud` - Legacy CRUD Generator

Original CRUD generation command with resource folder support.

```bash
php artisan generate:crud Post
```

## 🔧 Command Features

### Enhanced CRUD Generation Features

- **🎯 Selective Generation**: Choose specific CRUD methods to generate
- **📁 Optional Resources**: Skip resource folder creation when not needed
- **🔧 Modern PHP**: Constructor property promotion (PHP 8.0+)
- **📝 Clean Code**: Consistent naming conventions and structure
- **⚡ Rapid Development**: Generate complete CRUD structures in seconds
- **🏗️ SOLID Architecture**: Repository pattern with service layer separation

### Route Group Features

- **🚀 Automatic Registration**: Routes are automatically registered
- **🛡️ Middleware Integration**: Custom middleware assignment
- **📁 Organized Structure**: Clean route file organization
- **🔧 Flexible Configuration**: Customizable prefix and middleware
- **📝 Template System**: Uses customizable stub templates

## 📁 Template System

### Service Template (`stubs/service.stub`)
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

### Repository Template (`stubs/repository.stub`)
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

### Route Group Template (`stubs/route-group.stub`)
```php
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| {{name}} Routes
|--------------------------------------------------------------------------
|
| Here is where you can register {{name}} routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "{{middleware}}" middleware group.
|
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to {{name}} API',
        'status' => 'success'
    ]);
});
```

## 💡 Usage Examples

### Example 1: Blog Post CRUD
```bash
# Generate complete blog post structure
php artisan make:structure Post

# This creates:
# - app/Repositories/PostRepository.php
# - app/Services/Post/PostCreateService.php
# - app/Services/Post/PostReadService.php
# - app/Services/Post/PostUpdateService.php
# - app/Services/Post/PostDeleteService.php
# - app/Http/Resources/Post/Admin/
# - app/Http/Resources/Post/Api/
```

### Example 2: API-Only User Management
```bash
# Generate user services without resource folders
php artisan make:structure User --no-resource

# This creates:
# - app/Repositories/UserRepository.php
# - app/Services/User/UserCreateService.php
# - app/Services/User/UserReadService.php
# - app/Services/User/UserUpdateService.php
# - app/Services/User/UserDeleteService.php
```

### Example 3: Read-Only Product Catalog
```bash
# Generate only read operations for products
php artisan make:structure Product --only=read

# This creates:
# - app/Repositories/ProductRepository.php
# - app/Services/Product/ProductReadService.php
# - app/Http/Resources/Product/Admin/
# - app/Http/Resources/Product/Api/
```

### Example 4: Admin Route Group
```bash
# Create admin route group
php artisan make:route-group Admin --prefix=admin --middleware=admin

# This creates:
# - routes/admin.php
# - Registers middleware group 'admin'
# - Adds route group to bootstrap/app.php
```

## 🔄 Generated Code Examples

### Generated Service Class
```php
<?php

namespace App\Services\Post;

use App\Repositories\PostRepository;

class PostCreateService
{
    public function __construct(private PostRepository $postRepository) {}

    public function create(array $data)
    {
        // Your create logic goes here
        return $this->postRepository->create($data);
    }
}
```

### Generated Repository Class
```php
<?php

namespace App\Repositories;

use App\Models\Post;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class PostRepository extends BaseRepository
{
    public function model()
    {
        return Post::class;
    }

    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
}
```

### Generated Route File
```php
<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'message' => 'Welcome to Admin API',
        'status' => 'success'
    ]);
});

// Add your admin routes here
Route::apiResource('posts', PostController::class);
Route::apiResource('users', UserController::class);
```

## 🛠️ Customization

### Custom Stub Templates

You can customize the generated code by modifying the stub files:

1. **Service Stub**: `stubs/service.stub`
2. **Repository Stub**: `stubs/repository.stub`
3. **Route Group Stub**: `stubs/route-group.stub`

### Available Placeholders

- `{{model}}` - Model name (e.g., "Post")
- `{{className}}` - Full class name (e.g., "PostCreateService")
- `{{method}}` - CRUD method (e.g., "Create")
- `{{lowerMethod}}` - Lowercase method (e.g., "create")
- `{{lowerModel}}` - Lowercase model (e.g., "post")
- `{{name}}` - Route group name
- `{{middleware}}` - Middleware name

## 📊 Command Output Examples

### Successful Generation
```bash
$ php artisan make:structure Post --only=create,read
Repository, Service files, Resource folders (Admin, Api) created for Post with methods: create, read.

$ php artisan make:structure User --no-resource
Repository, Service files created for User with methods: create, read, update, delete.

$ php artisan make:route-group Admin --prefix=admin
✅ Route group 'Admin' created successfully!
📄 Using custom stub from stubs/route-group.stub
✅ Route file 'admin.php' created successfully.
✅ Route group 'Admin' registered in bootstrap/app.php
```

## 🧪 Testing Generated Code

### Testing Services
```php
class PostCreateServiceTest extends TestCase
{
    public function test_creates_post_successfully()
    {
        $mockRepository = Mockery::mock(PostRepository::class);
        $service = new PostCreateService($mockRepository);
        
        $postData = [
            'title' => 'Test Post',
            'content' => 'Test content'
        ];
        
        $mockRepository->shouldReceive('create')
            ->once()
            ->with($postData)
            ->andReturn(new Post($postData));
        
        $result = $service->create($postData);
        
        $this->assertInstanceOf(Post::class, $result);
    }
}
```

### Testing Routes
```php
class AdminRoutesTest extends TestCase
{
    public function test_admin_index_route()
    {
        $response = $this->get('/admin/');
        
        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Welcome to Admin API',
                    'status' => 'success'
                ]);
    }
}
```

## 📚 Best Practices

### Command Usage
1. **Start Small**: Use `--only` flag for specific operations
2. **Skip Unnecessary**: Use `--no-resource` when resources aren't needed
3. **Consistent Naming**: Use PascalCase for model names
4. **Test Generated Code**: Always test generated services and repositories

### Code Organization
1. **Follow Conventions**: Stick to Laravel naming conventions
2. **Add Business Logic**: Implement actual logic in generated service methods
3. **Handle Exceptions**: Add proper error handling
4. **Document Methods**: Add PHPDoc comments to generated methods

### Route Management
1. **Group Related Routes**: Use route groups for related functionality
2. **Apply Middleware**: Use appropriate middleware for security
3. **Version APIs**: Consider versioning for public APIs
4. **Document Endpoints**: Maintain API documentation

## 🔮 Future Enhancements

### Planned Features
- **Controller Generation**: Automatic controller creation with service injection
- **Request Validation**: Generate form request classes
- **API Documentation**: Automatic OpenAPI/Swagger documentation
- **Test Generation**: Automatic test class generation
- **Migration Integration**: Generate migrations alongside models
- **Seeder Generation**: Create seeders for generated models

### Advanced Templates
- **Custom Service Templates**: Different templates for different use cases
- **Repository Variants**: Templates for different repository patterns
- **Resource Templates**: Pre-built API resource templates
- **Controller Templates**: Various controller patterns