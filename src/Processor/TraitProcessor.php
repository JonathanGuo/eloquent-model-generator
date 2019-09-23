<?php

namespace JonathanGuo\EloquentModelGenerator\Processor;

use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\DateTimeType;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\SoftDeletes;
use JonathanGuo\CodeGenerator\Model\UseClassModel;
use JonathanGuo\CodeGenerator\Model\UseTraitModel;
use JonathanGuo\EloquentModelGenerator\Config;
use JonathanGuo\EloquentModelGenerator\Model\EloquentModel;
use JonathanGuo\EloquentModelGenerator\TypeRegistry;

class TraitProcessor implements ProcessorInterface
{
    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * FieldProcessor constructor.
     * @param DatabaseManager $databaseManager
     * @param TypeRegistry $typeRegistry
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * @param EloquentModel $model
     * @param Config $config
     * @throws SchemaException
     */
    public function process(EloquentModel $model, Config $config)
    {
        $schemaManager = $this->databaseManager->connection($config->get('connection'))->getDoctrineSchemaManager();
        $prefix = $this->databaseManager->connection($config->get('connection'))->getTablePrefix();

        $tableDetails = $schemaManager->listTableDetails($prefix . $model->getTableName());
        $this->addSoftDeletes($model, $tableDetails);
    }

    /**
     * @param EloquentModel $model
     * @param Table $tableDetails
     * @throws SchemaException
     */
    private function addSoftDeletes(EloquentModel $model, Table $tableDetails)
    {
        if (!$tableDetails->hasColumn('deleted_at')) {
            return;
        }

        $deletedAt = $tableDetails->getColumn('deleted_at');
        if ($deletedAt && $deletedAt->getType() instanceof DateTimeType) {
            $model->addUses(new UseClassModel(SoftDeletes::class));
            $model->addTrait(new UseTraitModel('SoftDeletes'));
        }
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return 6;
    }
}
