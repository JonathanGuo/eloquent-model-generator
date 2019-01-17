<?php

namespace JonathanGuo\EloquentModelGenerator\Processor;

use JonathanGuo\CodeGenerator\Model\NamespaceModel;
use JonathanGuo\EloquentModelGenerator\Config;
use JonathanGuo\EloquentModelGenerator\Model\EloquentModel;

/**
 * Class NamespaceProcessor
 * @package JonathanGuo\EloquentModelGenerator\Processor
 */
class NamespaceProcessor implements ProcessorInterface
{
    /**
     * @inheritdoc
     */
    public function process(EloquentModel $model, Config $config)
    {
        $model->setNamespace(new NamespaceModel($config->get('namespace')));
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 6;
    }
}
