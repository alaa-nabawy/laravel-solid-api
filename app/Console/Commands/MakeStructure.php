<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeStructure extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:structure {model} {--no-resource : Skip creating resource folders} {--only= : Only create specific CRUD methods (comma-separated: create,read,update,delete)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate CRUD structure with repository, services, and optional resource folders';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $model       = $this->argument('model');
        $noResource  = $this->option('no-resource');
        $onlyMethods = $this->option('only');

        // Create the service folder
        $serviceFolderPath = app_path("Services/{$model}");
        File::makeDirectory($serviceFolderPath, 0755, true, true);

        // Create the repository file
        $repositoryFilePath = app_path("Repositories/{$model}Repository.php");
        File::put($repositoryFilePath, $this->getStubContent('repository', $model));

        // Determine which CRUD methods to create
        $allCrudMethods = ['Create', 'Read', 'Update', 'Delete'];
        $crudMethods    = $allCrudMethods;

        if ($onlyMethods) {
            $requestedMethods = array_map('trim', explode(',', $onlyMethods));
            $crudMethods      = array_filter($allCrudMethods, function ($method) use ($requestedMethods) {
                return in_array(strtolower($method), array_map('strtolower', $requestedMethods));
            });
        }

        // Create the service files
        foreach ($crudMethods as $method) {
            $serviceFilePath = "{$serviceFolderPath}/{$model}{$method}Service.php";
            File::put($serviceFilePath, $this->getStubContent('service', $model, $method));
        }

        $createdItems = ['Repository', 'Service files'];

        // Create Resource folders: Admin and Api (unless --no-resource flag is used)
        if (! $noResource) {
            $resourceBasePath = app_path("Http/Resources/{$model}");
            $adminPath        = "{$resourceBasePath}/Admin";
            $apiPath          = "{$resourceBasePath}/Api";

            File::makeDirectory($adminPath, 0755, true, true);
            File::makeDirectory($apiPath, 0755, true, true);

            $createdItems[] = 'Resource folders (Admin, Api)';
        }

        $methodsList = implode(', ', array_map('strtolower', $crudMethods));
        $itemsList   = implode(', ', $createdItems);

        $this->info("{$itemsList} created for {$model} with methods: {$methodsList}.");
    }

    protected function getStubContent(string $type, string $model, string $method = ''): string
    {
        $stubPath = base_path("stubs/{$type}.stub");
        $content  = File::get($stubPath);

        // Replace placeholders with actual content
        $content = str_replace('{{model}}', $model, $content);
        $content = str_replace('{{className}}', $model . $method, $content);
        $content = str_replace('{{method}}', $method, $content);
        $content = str_replace('{{lowerMethod}}', strtolower($method), $content);
        $content = str_replace('{{lowerModel}}', strtolower($model), $content);

        return $content;
    }

    protected function generateService(string $service, string $repository): void
    {
        $servicePath = app_path('Services/' . $service . '.php');

        if (! file_exists($servicePath)) {
            $serviceTemplate = str_replace(
                ['{{ServiceName}}', '{{RepositoryName}}', '{{repositoryName}}'],
                [$service, $repository, lcfirst($repository)],
                file_get_contents(base_path('stubs/service.stub'))
            );

            file_put_contents($servicePath, $serviceTemplate);
        }

        $this->info('Service files generated.');
    }

    protected function generateRepository(string $repository, string $mainModel): void
    {
        $stubPath       = base_path('stubs/repository.stub');
        $repositoryPath = app_path('Repositories/' . $repository . '.php');

        if (! file_exists($repositoryPath)) {
            $repositoryTemplate = str_replace(
                ['{{RepositoryName}}', '{{MainModelClass}}'],
                [$repository, $mainModel],
                file_get_contents($stubPath)
            );

            file_put_contents($repositoryPath, $repositoryTemplate);
        }

        $this->info('Repository files generated.');
    }
}
