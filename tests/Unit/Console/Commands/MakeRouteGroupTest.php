<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\MakeRouteGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MakeRouteGroupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clean up any test files
        $this->cleanupTestFiles();
    }

    protected function tearDown(): void
    {
        $this->cleanupTestFiles();
        parent::tearDown();
    }

    private function cleanupTestFiles(): void
    {
        $testFiles = [
            base_path('routes/test-route.php'),
            base_path('routes/admin-panel.php'),
        ];

        foreach ($testFiles as $file) {
            if (File::exists($file)) {
                File::delete($file);
            }
        }
    }

    public function test_command_creates_route_file_with_default_options(): void
    {
        $exitCode = Artisan::call('make:route-group', ['name' => 'TestRoute']);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::exists(base_path('routes/test-route.php')));

        $content = File::get(base_path('routes/test-route.php'));
        $this->assertStringContainsString('TestRoute Routes', $content);
        $this->assertStringContainsString('Welcome to TestRoute API', $content);
    }

    public function test_command_creates_route_file_with_custom_options(): void
    {
        $exitCode = Artisan::call('make:route-group', [
            'name'         => 'AdminPanel',
            '--prefix'     => 'admin',
            '--middleware' => 'admin_auth',
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue(File::exists(base_path('routes/admin-panel.php')));

        $content = File::get(base_path('routes/admin-panel.php'));
        $this->assertStringContainsString('AdminPanel Routes', $content);
        $this->assertStringContainsString('Welcome to AdminPanel API', $content);
    }

    public function test_command_skips_existing_route_file(): void
    {
        // Create a test route file first
        File::put(base_path('routes/test-route.php'), 'existing content');

        $exitCode = Artisan::call('make:route-group', ['name' => 'TestRoute']);

        $this->assertEquals(0, $exitCode);
        $content = File::get(base_path('routes/test-route.php'));
        $this->assertEquals('existing content', $content);

        $output = Artisan::output();
        $this->assertStringContainsString('already exists, skipping creation', $output);
    }

    public function test_command_uses_custom_stub_when_available(): void
    {
        $exitCode = Artisan::call('make:route-group', ['name' => 'TestRoute']);

        $this->assertEquals(0, $exitCode);
        $content = File::get(base_path('routes/test-route.php'));
        $this->assertStringContainsString('Custom stub for TestRoute with test_route', $content);
    }

    public function test_validate_php_syntax_method(): void
    {
        $command    = new MakeRouteGroup;
        $reflection = new \ReflectionClass($command);
        $method     = $reflection->getMethod('validatePhpSyntax');
        $method->setAccessible(true);

        // Test valid PHP code
        $validCode = "<?php\n\necho 'Hello World';";
        $this->assertTrue($method->invoke($command, $validCode));

        // Test invalid PHP code
        $invalidCode = "<?php\n\necho 'Hello World'";
        $this->assertFalse($method->invoke($command, $invalidCode));
    }

    public function test_find_matching_brace_method(): void
    {
        $command    = new MakeRouteGroup;
        $reflection = new \ReflectionClass($command);
        $method     = $reflection->getMethod('findMatchingBrace');
        $method->setAccessible(true);

        $content = 'function test() { return true; }';
        $result  = $method->invoke($command, $content, 15); // Position of opening brace
        $this->assertEquals(31, $result); // Position of closing brace

        // Test nested braces
        $nestedContent = 'function test() { if (true) { return false; } return true; }';
        $result        = $method->invoke($command, $nestedContent, 15);
        $this->assertEquals(59, $result);
    }

    public function test_ensure_route_import_method(): void
    {
        $command = new MakeRouteGroup;

        // Mock the output to avoid null reference errors
        $output = $this->createMock(\Illuminate\Console\OutputStyle::class);
        $command->setOutput($output);

        $reflection = new \ReflectionClass($command);
        $method     = $reflection->getMethod('ensureRouteImport');
        $method->setAccessible(true);

        // Test content without Route import
        $contentWithoutImport = "<?php\n\nuse Illuminate\Foundation\Application;\n\nreturn Application::configure();";
        $result               = $method->invoke($command, $contentWithoutImport);
        $this->assertStringContainsString('use Illuminate\Support\Facades\Route;', $result);

        // Test content with existing Route import
        $contentWithImport = "<?php\n\nuse Illuminate\Support\Facades\Route;\n\nreturn Application::configure();";
        $result            = $method->invoke($command, $contentWithImport);
        $this->assertEquals($contentWithImport, $result);
    }

    public function test_command_signature_and_description(): void
    {
        $command    = new MakeRouteGroup;
        $reflection = new \ReflectionClass($command);

        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);
        $signature = $signatureProperty->getValue($command);

        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);
        $description = $descriptionProperty->getValue($command);

        $this->assertEquals(
            'make:route-group {name} {--prefix= : Route prefix for the group} {--middleware= : Middleware for the route group}',
            $signature
        );

        $this->assertEquals(
            'Create a new route group with file, middleware, and registration',
            $description
        );
    }
}
