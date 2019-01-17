<?php

namespace JonathanGuo\EloquentModelGenerator\Provider;

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

        $this->app->tag([
            ExistenceCheckerProcessor::class,
            FieldProcessor::class,
            NamespaceProcessor::class,
            RelationProcessor::class,
            CustomPropertyProcessor::class,
            TableNameProcessor::class,
            CustomPrimaryKeyProcessor::class,
        ], self::PROCESSOR_TAG);

        $this->app->bind(EloquentModelBuilder::class, function ($app) {
            return new EloquentModelBuilder($app->tagged(self::PROCESSOR_TAG));
        });
    }
}
