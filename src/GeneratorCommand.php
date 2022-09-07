<?php

namespace ShipperDev\SwaggerApiDocGenerator;

use Error;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;

class GeneratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api-doc:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate project api documentation.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->components->alert("Start api doc generation");

        $config = collect(config('api_doc_generator'));

        if ($config->isEmpty()) {
            $this->components->info('Api docs config empty.');
            return 0;
        }

        $config->each(function ($item, $key) {
            $this->newLine();
            $this->components->info("Start generation " . Str::headline($key));
            $envs  = collect($item['env']);
            $paths = collect($item['paths']);
            $file_name = empty($item['file_name']) ? "$key.json" : "{$item['file_name']}.json";

            if ($paths->isEmpty()) {
                $this->components->error("Generation failed, scan path is empty.");
                return 0;
            }

            if ($envs->isNotEmpty()) {
                try {
                    $envs->each(
                        fn($env_value, $env_name) => define($env_name, $env_value)
                    );
                } catch (Exception | Error $e) {
                    $this->components->error("Generation failed {$e->getMessage()}.");
                    $this->components->error($e->getTraceAsString());
                    return 0;
                }
            }

            try {
                $types = Schema::$_types;
                $types['multipleOf'] = "number";
                Schema::$_types = $types;

                $openapi = Generator::scan($paths->toArray());
                Storage::disk('public')->put('api-doc/' . $file_name, $openapi->toJson());
                $this->components->info(Str::headline($key) . ' generation finished.');
            } catch (Exception | Error $e) {
                $this->components->error("Generation failed {$e->getMessage()}.");
                return 0;
            }
        });

        $this->components->alert("Generation finished.");
        return 0;
    }
}
