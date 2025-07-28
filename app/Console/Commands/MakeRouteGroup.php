<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeRouteGroup extends Command
{
    protected $signature = 'make:route-group {name} {--prefix= : Route prefix for the group} {--middleware= : Middleware for the route group}';

    protected $description = 'Create a new route group with file, middleware, and registration';

    public function handle(): void
    {
        $name       = $this->argument('name');
        $prefix     = $this->option('prefix') ?: Str::kebab($name);
        $middleware = $this->option('middleware') ?: Str::snake($name);

        $this->info("Creating route group '{$name}' with prefix '{$prefix}' and middleware '{$middleware}'...");

        // Step 1: Create route file
        $this->createRouteFile($name, $prefix, $middleware);

        // Step 2: Add middleware group
        $this->addMiddlewareGroup($middleware);

        // Step 3: Register route group
        $this->registerRouteGroup($name, $prefix, $middleware);

        $this->info("✅ Route group '{$name}' created successfully!");
    }

    protected function createRouteFile(string $name, string $prefix, string $middleware): void
    {
        $routeFileName = Str::kebab($name) . '.php';
        $routeFilePath = base_path('routes/' . $routeFileName);

        if (File::exists($routeFilePath)) {
            $this->warn("⚠️  Route file '{$routeFileName}' already exists, skipping creation.");

            return;
        }

        $stubPath    = base_path('stubs/route-group.stub');
        $defaultStub = "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n/*\n|--------------------------------------------------------------------------\n| {$name} Routes\n|--------------------------------------------------------------------------\n*/\n\nRoute::get('/', function () {\n    return response()->json(['message' => 'Welcome to {$name} API']);\n});\n";

        if (File::exists($stubPath)) {
            $content = File::get($stubPath);
            $this->info('📄 Using custom stub from stubs/route-group.stub');
        } else {
            $content = $defaultStub;
            $this->info('📄 Using default route template');
        }

        // Replace placeholders
        $content = str_replace(['{{name}}', '{{middleware}}'], [$name, $middleware], $content);

        File::put($routeFilePath, $content);
        $this->info("✅ Route file '{$routeFileName}' created successfully.");
    }

    protected function addMiddlewareGroup(string $middleware): void
    {
        $appPath    = base_path('bootstrap/app.php');
        $appContent = File::get($appPath);

        // Check if middleware group already exists
        if (strpos($appContent, "'{$middleware}'") !== false) {
            $this->warn("⚠️  Middleware group '{$middleware}' already exists, skipping creation.");

            return;
        }

        // More robust pattern to find withMiddleware section
        $middlewarePattern = '/->withMiddleware\s*\(\s*function\s*\(\s*Middleware\s+\$middleware\s*\)\s*:\s*void\s*\{([^}]*)\}\s*\)/s';

        if (preg_match($middlewarePattern, $appContent, $matches)) {
            $middlewareContent  = trim($matches[1]);
            $newMiddlewareGroup = "\n        \$middleware->group('{$middleware}', [\n            // Add your middleware here\n        ]);";

            // Add proper spacing if there's existing content
            if (! empty($middlewareContent) && ! preg_match('/^\s*\/\/\s*$/', $middlewareContent)) {
                $updatedMiddlewareContent = $middlewareContent . $newMiddlewareGroup;
            } else {
                $updatedMiddlewareContent = $newMiddlewareGroup;
            }

            $updatedAppContent = str_replace($matches[0], "->withMiddleware(function (Middleware \$middleware): void {{$updatedMiddlewareContent}\n    })", $appContent);

            // Validate syntax before saving
            if ($this->validatePhpSyntax($updatedAppContent)) {
                File::put($appPath, $updatedAppContent);
                $this->info("✅ Middleware group '{$middleware}' added to bootstrap/app.php");
            } else {
                $this->error('❌ Generated code has syntax errors, aborting modification');
            }
        } else {
            $this->error('❌ Could not find withMiddleware section in bootstrap/app.php');
        }
    }

    protected function registerRouteGroup(string $name, string $prefix, string $middleware): void
    {
        $appPath       = base_path('bootstrap/app.php');
        $appContent    = File::get($appPath);
        $routeFileName = Str::kebab($name) . '.php';

        // Check if route is already registered
        if (strpos($appContent, "routes/{$routeFileName}") !== false) {
            $this->warn("⚠️  Route group '{$name}' already registered, skipping registration.");

            return;
        }

        // Ensure Route facade is imported
        $appContent = $this->ensureRouteImport($appContent);

        // Use a simpler approach: find the closing of withRouting and insert before it
        $withRoutingStart = strpos($appContent, '->withRouting(');
        if ($withRoutingStart === false) {
            $this->error('❌ Could not find withRouting section in bootstrap/app.php');

            return;
        }

        // Find the matching closing parenthesis for withRouting
        $openParens     = 0;
        $pos            = $withRoutingStart + strlen('->withRouting(');
        $withRoutingEnd = false;

        while ($pos < strlen($appContent)) {
            $char = $appContent[$pos];
            if ($char === '(') {
                $openParens++;
            } elseif ($char === ')') {
                if ($openParens === 0) {
                    $withRoutingEnd = $pos;
                    break;
                }
                $openParens--;
            }
            $pos++;
        }

        if ($withRoutingEnd === false) {
            $this->error('❌ Could not find end of withRouting section');

            return;
        }

        // Extract the content between withRouting parentheses
        $routingContent = substr($appContent, $withRoutingStart + strlen('->withRouting('), $withRoutingEnd - $withRoutingStart - strlen('->withRouting('));

        // Check if 'using:' already exists
        if (strpos($routingContent, 'using:') !== false) {
            // Find the using function and append to it
            $usingStart = strpos($routingContent, 'using: function ()');
            if ($usingStart !== false) {
                $funcStart = strpos($routingContent, '{', $usingStart);
                $funcEnd   = $this->findMatchingBrace($routingContent, $funcStart);

                if ($funcStart !== false && $funcEnd !== false) {
                    $beforeFunc = substr($routingContent, 0, $funcEnd);
                    $afterFunc  = substr($routingContent, $funcEnd);

                    $newRouteCode = "\n            Route::middleware('{$middleware}')\n                ->prefix('{$prefix}')\n                ->group(base_path('routes/{$routeFileName}'));";

                    $updatedRoutingContent = $beforeFunc . $newRouteCode . $afterFunc;
                } else {
                    $this->error("❌ Could not parse existing 'using' function");

                    return;
                }
            } else {
                $this->error("❌ Found 'using:' but could not locate function");

                return;
            }
        } else {
            // Add new using parameter
            $routingContent = trim($routingContent);
            if (! empty($routingContent) && ! str_ends_with($routingContent, ',')) {
                $routingContent .= ',';
            }
            $newRoute              = "\n        using: function () {\n            Route::middleware('{$middleware}')\n                ->prefix('{$prefix}')\n                ->group(base_path('routes/{$routeFileName}'));\n        }";
            $updatedRoutingContent = $routingContent . $newRoute;
        }

        // Reconstruct the full content
        $beforeRouting     = substr($appContent, 0, $withRoutingStart + strlen('->withRouting('));
        $afterRouting      = substr($appContent, $withRoutingEnd);
        $updatedAppContent = $beforeRouting . $updatedRoutingContent . $afterRouting;

        // Validate syntax before saving
        if ($this->validatePhpSyntax($updatedAppContent)) {
            File::put($appPath, $updatedAppContent);
            $this->info("✅ Route group '{$name}' registered in bootstrap/app.php");
        } else {
            $this->error('❌ Generated code has syntax errors, aborting modification');
            $this->warn('💡 Try running the command again or check bootstrap/app.php manually');
        }
    }

    protected function findMatchingBrace(string $content, int $start): int|false
    {
        $openBraces = 0;
        $pos        = $start;

        while ($pos < strlen($content)) {
            $char = $content[$pos];
            if ($char === '{') {
                $openBraces++;
            } elseif ($char === '}') {
                $openBraces--;
                if ($openBraces === 0) {
                    return $pos;
                }
            }
            $pos++;
        }

        return false;
    }

    protected function ensureRouteImport(string $content): string
    {
        // Check if Route is already imported
        if (strpos($content, 'use Illuminate\Support\Facades\Route;') !== false) {
            return $content;
        }

        // Find the <?php opening tag
        $phpOpenPos = strpos($content, '<?php');
        if ($phpOpenPos === false) {
            return $content;
        }

        // Find the end of the opening line
        $firstLineEnd = strpos($content, "\n", $phpOpenPos);
        if ($firstLineEnd === false) {
            $firstLineEnd = strlen($content);
        }

        // Insert the Route import after the <?php line
        $beforeImport = substr($content, 0, $firstLineEnd + 1);
        $afterImport  = substr($content, $firstLineEnd + 1);

        $routeImport = "\nuse Illuminate\\Support\\Facades\\Route;";

        $this->info('✅ Added Route facade import to bootstrap/app.php');

        return $beforeImport . $routeImport . $afterImport;
    }

    protected function validatePhpSyntax(string $code): bool
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'laravel_syntax_check');
        file_put_contents($tempFile, $code);

        $output     = [];
        $returnCode = 0;
        exec("php -l {$tempFile} 2>&1", $output, $returnCode);

        unlink($tempFile);

        return $returnCode === 0;
    }
}
