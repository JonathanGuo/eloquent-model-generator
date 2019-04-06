<?php

namespace JonathanGuo\EloquentModelGenerator\Provider;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use JonathanGuo\EloquentModelGenerator\Command\GenerateModelCommand;
use JonathanGuo\EloquentModelGenerator\EloquentModelBuilder;
use JonathanGuo\EloquentModelGenerator\Processor\CustomPrimaryKeyProcessor;
use JonathanGuo\EloquentModelGenerator\Processor\CustomPropertyProcessor;
use JonathanGuo\EloquentModelGenerator\Processor\ExistenceCheckerProcessor;
use JonathanGuo\EloquentModelGenerator\Processor\FieldProcessor;
use JonathanGuo\EloquentModelGenerator\Processor\NamespaceProcessor;
use JonathanGuo\EloquentModelGenerator\Processor\RelationProcessor;
use JonathanGuo\EloquentModelGenerator\Processor\TableNameProcessor;

/**
 * Class GeneratorServiceProvider
 * @package JonathanGuo\EloquentModelGenerator\Provider
 */
class GeneratorServiceProvider extends ServiceProvider
{
    const PROCESSOR_TAG = 'eloquent_model_generator.processor';

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this->commands([
            GenerateModelCommand::class,
        ]);

        $this->app->bind(EloquentModelBuilder::class, function (Application $app) {
            return new EloquentModelBuilder(array_map(function ($processor) use ($app) {
                return $app->make($processor);
            }, [
                ExistenceCheckerProcessor::class,
                FieldProcessor::class,
                NamespaceProcessor::class,
                RelationProcessor::class,
                CustomPropertyProcessor::class,
                TableNameProcessor::class,
                CustomPrimaryKeyProcessor::class,
            ]));
        });
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Resources/config.php' => config_path('eloquent_model_generator.php'),
        ]);
    }
}
