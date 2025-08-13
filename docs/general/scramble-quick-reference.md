# Scramble Quick Reference Guide

A quick reference for developers working with Scramble API documentation in this Laravel project.

## Quick Start

### Access Documentation

```bash
# Start Laravel server
php artisan serve

# Open documentation in browser
open http://localhost:8000/docs/api
```

### Export Documentation

```bash
# Export OpenAPI specification
php artisan scramble:export

# Export to custom path
php artisan scramble:export --path=public/api-docs.json
```

## Development Workflow

### 1. Create API Endpoint

#### Step 1: Create Controller

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;

class PostController extends Controller
{
    public function store(StorePostRequest $request): PostResource
    {
        $post = Post::create($request->validated());
        return new PostResource($post);
    }
}
```

#### Step 2: Create FormRequest

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'published' => ['boolean'],
        ];
    }
}
```

#### Step 3: Create API Resource

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'published' => $this->published,
            'created_at' => $this->created_at->toISOString(),
            // Conditional field
            $this->mergeWhen($this->published, [
                'published_at' => $this->published_at?->toISOString(),
            ]),
        ];
    }
}
```

#### Step 4: Add Route

```php
// routes/api.php
Route::prefix('v1')->group(function () {
    Route::apiResource('posts', PostController::class);
});
```

### 2. Documentation Updates Automatically

- Refresh the documentation page
- New endpoints appear automatically
- Request/response schemas are generated
- Validation rules are documented

## Common Patterns

### Pagination

```php
public function index(Request $request): AnonymousResourceCollection
{
    $posts = Post::paginate($request->per_page ?? 15);
    return PostResource::collection($posts);
}
```

### Search/Filtering

```php
public function index(Request $request): AnonymousResourceCollection
{
    $posts = Post::query()
        ->when($request->search, function ($query, $search) {
            $query->where('title', 'like', "%{$search}%");
        })
        ->when($request->status, function ($query, $status) {
            $query->where('status', $status);
        })
        ->paginate();

    return PostResource::collection($posts);
}
```

### Conditional Responses

```php
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,

            // Only show to authenticated user
            $this->mergeWhen($request->user()?->id === $this->id, [
                'email_verified_at' => $this->email_verified_at,
                'created_at' => $this->created_at,
            ]),

            // Only show to admins
            $this->mergeWhen($request->user()?->isAdmin(), [
                'last_login_at' => $this->last_login_at,
                'ip_address' => $this->last_login_ip,
            ]),
        ];
    }
}
```

### Error Responses

```php
public function show(Post $post): PostResource
{
    // Laravel automatically returns 404 for missing models
    return new PostResource($post);
}

public function destroy(Post $post): JsonResponse
{
    if (!$post->canDelete()) {
        return response()->json([
            'message' => 'Cannot delete published post'
        ], 422);
    }

    $post->delete();

    return response()->json([
        'message' => 'Post deleted successfully'
    ]);
}
```

## Validation Patterns

### Basic Validation

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users'],
        'age' => ['required', 'integer', 'min:18', 'max:120'],
        'tags' => ['array'],
        'tags.*' => ['string', 'max:50'],
    ];
}
```

### Conditional Validation

```php
public function rules(): array
{
    return [
        'type' => ['required', 'in:individual,company'],
        'name' => ['required', 'string'],
        'company_name' => ['required_if:type,company', 'string'],
        'tax_id' => ['required_if:type,company', 'string'],
    ];
}
```

### Update Validation

```php
public function rules(): array
{
    return [
        'name' => ['sometimes', 'required', 'string', 'max:255'],
        'email' => [
            'sometimes',
            'required',
            'email',
            'unique:users,email,' . $this->user->id
        ],
    ];
}
```

## Response Patterns

### Success Responses

```php
// Single resource
return new UserResource($user);

// Collection
return UserResource::collection($users);

// Paginated collection
return UserResource::collection(User::paginate());

// Custom response
return response()->json([
    'message' => 'Operation successful',
    'data' => new UserResource($user)
], 201);
```

### Error Responses

```php
// Validation error (automatic)
// Returns 422 with validation errors

// Not found (automatic with route model binding)
// Returns 404

// Custom error
return response()->json([
    'message' => 'Custom error message',
    'errors' => ['field' => ['Error description']]
], 400);
```

## Configuration Tips

### Environment Variables

```env
# .env
API_VERSION=1.0.0
APP_ENV=local  # Affects documentation access
```

### Custom API Path

```php
// config/scramble.php
'api_path' => 'api/v1',  // Only document routes starting with /api/v1
```

### Custom Servers

```php
// config/scramble.php
'servers' => [
    'Development' => 'http://localhost:8000/api',
    'Staging' => 'https://staging.example.com/api',
    'Production' => 'https://api.example.com',
],
```

## Debugging

### Check Routes

```bash
# List all API routes
php artisan route:list --path=api

# Clear route cache
php artisan route:clear
```

### Validation Issues

```bash
# Check if FormRequest is being used
php artisan route:list --path=api --columns=method,uri,action
```

### Resource Issues

```bash
# Test resource output
php artisan tinker
>>> $user = App\Models\User::first();
>>> new App\Http\Resources\UserResource($user);
```

## Best Practices

### ✅ Do

- Use FormRequest classes for validation
- Use API Resources for responses
- Add proper return type hints
- Use route model binding
- Keep validation rules in FormRequests
- Use descriptive method names

### ❌ Don't

- Use inline validation in controllers
- Return raw arrays from controllers
- Mix validation logic in controllers
- Use generic method names
- Forget return type hints

## Troubleshooting

| Issue                      | Solution                                                 |
| -------------------------- | -------------------------------------------------------- |
| Routes not showing         | Check `api_path` config, ensure routes start with `/api` |
| Validation not documented  | Use FormRequest classes instead of inline validation     |
| Response schema missing    | Use API Resources instead of raw arrays                  |
| Documentation not updating | Clear route cache: `php artisan route:clear`             |
| Access denied              | Check middleware configuration in `config/scramble.php`  |

## Quick Commands

```bash
# Generate FormRequest
php artisan make:request StoreUserRequest

# Generate API Resource
php artisan make:resource UserResource

# Generate Controller
php artisan make:controller Api/UserController --api

# Generate complete API resource controller
php artisan make:controller Api/UserController --api --resource
```
