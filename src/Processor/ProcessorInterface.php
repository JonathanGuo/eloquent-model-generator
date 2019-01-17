<?php

namespace JonathanGuo\EloquentModelGenerator\Processor;

use JonathanGuo\EloquentModelGenerator\Config;
use JonathanGuo\EloquentModelGenerator\Model\EloquentModel;

/**
 * Interface ProcessorInterface
 * @package JonathanGuo\EloquentModelGenerator\Processor
 */
interface ProcessorInterface
{
    /**
     * @param EloquentModel $model
     * @param Config $config
     */
    public function process(EloquentModel $model, Config $config);

    /**
     * @return int
     */
    public function getPriority();
}
