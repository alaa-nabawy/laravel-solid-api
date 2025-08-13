<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\MakeStructure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MakeStructureTest extends TestCase
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
        $testPaths = [
            app_path('Services/Product'),
            app_path('Services/Order'),
            app_path('Repositories/ProductRepository.php'),
            app_path('Repositories/OrderRepository.php'),
            app_path('Http/Resources/Product'),
            app_path('Http/Resources/Order'),
        ];

        foreach ($testPaths as $path) {
            if (File::exists($path)) {
                if (File::isDirectory($path)) {
                    File::deleteDirectory($path);
                } else {
                    File::delete($path);
                }
            }
        }
    }

    public function test_command_creates_full_crud_structure(): void
    {
        $exitCode = Artisan::call('make:structure', ['model' => 'Product']);

        $this->assertEquals(0, $exitCode);

        // Check repository file
        $this->assertTrue(File::exists(app_path('Repositories/ProductRepository.php')));

        // Check service files
        $this->assertTrue(File::exists(app_path('Services/Product/ProductCreateService.php')));
        $this->assertTrue(File::exists(app_path('Services/Product/ProductReadService.php')));
        $this->assertTrue(File::exists(app_path('Services/Product/ProductUpdateService.php')));
        $this->assertTrue(File::exists(app_path('Services/Product/ProductDeleteService.php')));

        // Check resource folders
        $this->assertTrue(File::isDirectory(app_path('Http/Resources/Product/Admin')));
        $this->assertTrue(File::isDirectory(app_path('Http/Resources/Product/Api')));

        $output = Artisan::output();
        $this->assertStringContainsString('Repository, Service files, Resource folders (Admin, Api) created for Product', $output);
        $this->assertStringContainsString('with methods: create, read, update, delete', $output);
    }

    public function test_command_creates_structure_without_resources(): void
    {
        $exitCode = Artisan::call('make:structure', [
            'model'         => 'Order',
            '--no-resource' => true,
        ]);

        $this->assertEquals(0, $exitCode);

        // Check repository file
        $this->assertTrue(File::exists(app_path('Repositories/OrderRepository.php')));

        // Check service files
        $this->assertTrue(File::exists(app_path('Services/Order/OrderCreateService.php')));
        $this->assertTrue(File::exists(app_path('Services/Order/OrderReadService.php')));
        $this->assertTrue(File::exists(app_path('Services/Order/OrderUpdateService.php')));
        $this->assertTrue(File::exists(app_path('Services/Order/OrderDeleteService.php')));

        // Check resource folders are NOT created
        $this->assertFalse(File::exists(app_path('Http/Resources/Order')));

        $output = Artisan::output();
        $this->assertStringContainsString('Repository, Service files created for Order', $output);
        $this->assertStringNotContainsString('Resource folders', $output);
    }

    public function test_command_creates_only_specified_methods(): void
    {
        $exitCode = Artisan::call('make:structure', [
            'model'  => 'Product',
            '--only' => 'create,read',
        ]);

        $this->assertEquals(0, $exitCode);

        // Check repository file
        $this->assertTrue(File::exists(app_path('Repositories/ProductRepository.php')));

        // Check only specified service files are created
        $this->assertTrue(File::exists(app_path('Services/Product/ProductCreateService.php')));
        $this->assertTrue(File::exists(app_path('Services/Product/ProductReadService.php')));
        $this->assertFalse(File::exists(app_path('Services/Product/ProductUpdateService.php')));
        $this->assertFalse(File::exists(app_path('Services/Product/ProductDeleteService.php')));

        $output = Artisan::output();
        $this->assertStringContainsString('with methods: create, read', $output);
    }

    public function test_command_creates_only_specified_methods_case_insensitive(): void
    {
        $exitCode = Artisan::call('make:structure', [
            'model'  => 'Product',
            '--only' => 'CREATE,UPDATE',
        ]);

        $this->assertEquals(0, $exitCode);

        // Check only specified service files are created
        $this->assertTrue(File::exists(app_path('Services/Product/ProductCreateService.php')));
        $this->assertFalse(File::exists(app_path('Services/Product/ProductReadService.php')));
        $this->assertTrue(File::exists(app_path('Services/Product/ProductUpdateService.php')));
        $this->assertFalse(File::exists(app_path('Services/Product/ProductDeleteService.php')));

        $output = Artisan::output();
        $this->assertStringContainsString('with methods: create, update', $output);
    }

    public function test_get_stub_content_method(): void
    {
        $command    = new MakeStructure;
        $reflection = new \ReflectionClass($command);
        $method     = $reflection->getMethod('getStubContent');
        $method->setAccessible(true);

        // Test repository stub
        $repositoryContent = $method->invoke($command, 'repository', 'Product');
        $this->assertStringContainsString('ProductRepository', $repositoryContent);
        $this->assertStringContainsString('namespace App\\Repositories', $repositoryContent);

        // Test service stub
        $serviceContent = $method->invoke($command, 'service', 'Product', 'Create');
        $this->assertStringContainsString('ProductCreateService', $serviceContent);
        $this->assertStringContainsString('namespace App\\Services\\Product', $serviceContent);
        $this->assertStringContainsString('Create service methods', $serviceContent);
    }

    public function test_generate_service_method(): void
    {
        $command = new MakeStructure;

        // Mock the output to avoid null reference errors
        $output = $this->createMock(\Illuminate\Console\OutputStyle::class);
        $command->setOutput($output);

        $reflection = new \ReflectionClass($command);
        $method     = $reflection->getMethod('generateService');
        $method->setAccessible(true);

        // This method should create a service file
        $method->invoke($command, 'ProductService', 'ProductRepository');

        $this->assertTrue(File::exists(app_path('Services/ProductService.php')));

        // Clean up
        File::delete(app_path('Services/ProductService.php'));
    }

    public function test_generate_repository_method(): void
    {
        $command = new MakeStructure;

        // Mock the output to avoid null reference errors
        $output = $this->createMock(\Illuminate\Console\OutputStyle::class);
        $command->setOutput($output);

        $reflection = new \ReflectionClass($command);
        $method     = $reflection->getMethod('generateRepository');
        $method->setAccessible(true);

        // This method should create a repository file
        $method->invoke($command, 'ProductRepository', 'Product');

        $this->assertTrue(File::exists(app_path('Repositories/ProductRepository.php')));

        $content = File::get(app_path('Repositories/ProductRepository.php'));
        $this->assertStringContainsString('ProductRepository', $content);
        $this->assertStringContainsString('Product', $content);

        // Clean up
        File::delete(app_path('Repositories/ProductRepository.php'));
    }

    public function test_command_signature_and_description(): void
    {
        $command    = new MakeStructure;
        $reflection = new \ReflectionClass($command);

        $signatureProperty = $reflection->getProperty('signature');
        $signatureProperty->setAccessible(true);
        $signature = $signatureProperty->getValue($command);

        $descriptionProperty = $reflection->getProperty('description');
        $descriptionProperty->setAccessible(true);
        $description = $descriptionProperty->getValue($command);

        $this->assertEquals(
            'make:structure {model} {--no-resource : Skip creating resource folders} {--only= : Only create specific CRUD methods (comma-separated: create,read,update,delete)}',
            $signature
        );

        $this->assertEquals(
            'Generate CRUD structure with repository, services, and optional resource folders',
            $description
        );
    }

    public function test_command_handles_invalid_only_methods(): void
    {
        $exitCode = Artisan::call('make:structure', [
            'model'  => 'Product',
            '--only' => 'invalid,create,unknown',
        ]);

        $this->assertEquals(0, $exitCode);

        // Should only create the valid method (create)
        $this->assertTrue(File::exists(app_path('Services/Product/ProductCreateService.php')));
        $this->assertFalse(File::exists(app_path('Services/Product/ProductReadService.php')));
        $this->assertFalse(File::exists(app_path('Services/Product/ProductUpdateService.php')));
        $this->assertFalse(File::exists(app_path('Services/Product/ProductDeleteService.php')));

        $output = Artisan::output();
        $this->assertStringContainsString('with methods: create', $output);
    }
}
