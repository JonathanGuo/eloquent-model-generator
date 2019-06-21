<?php

namespace JonathanGuo\EloquentModelGenerator\Processor;

use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Illuminate\Database\DatabaseManager;
use JonathanGuo\CodeGenerator\Model\DocBlockModel;
use JonathanGuo\CodeGenerator\Model\PropertyModel;
use JonathanGuo\CodeGenerator\Model\VirtualPropertyModel;
use JonathanGuo\EloquentModelGenerator\Config;
use JonathanGuo\EloquentModelGenerator\Model\EloquentModel;
use JonathanGuo\EloquentModelGenerator\TypeRegistry;

/**
 * Class FieldProcessor
 * @package JonathanGuo\EloquentModelGenerator\Processor
 */
class FieldProcessor implements ProcessorInterface
{
    /**
     * @var DatabaseManager
     */
    protected $databaseManager;

    /**
     * @var TypeRegistry
     */
    protected $typeRegistry;

    /**
     * FieldProcessor constructor.
     * @param DatabaseManager $databaseManager
     * @param TypeRegistry $typeRegistry
     */
    public function __construct(DatabaseManager $databaseManager, TypeRegistry $typeRegistry)
    {
        $this->databaseManager = $databaseManager;
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * @inheritdoc
     */
    public function process(EloquentModel $model, Config $config)
    {
        $schemaManager = $this->databaseManager->connection($config->get('connection'))->getDoctrineSchemaManager();
        $prefix = $this->databaseManager->connection($config->get('connection'))->getTablePrefix();

        $tableDetails = $schemaManager->listTableDetails($prefix . $model->getTableName());
        $foreignKeys = $schemaManager->listTableForeignKeys($prefix . $model->getTableName());
        $primaryColumnNames = $tableDetails->getPrimaryKey() ? $tableDetails->getPrimaryKey()->getColumns() : [];
        $timestampColumns = ['created_at', 'updated_at', 'deleted_at'];
        $foreignKeyColumns = array_reduce($foreignKeys, function (array $carry, ForeignKeyConstraint $foreignKeyConstraint) {
            return array_merge($carry, $foreignKeyConstraint->getColumns());
        }, []);
        $skippingColumns = array_merge($primaryColumnNames, $timestampColumns, $foreignKeyColumns);

        $fillable = [];
        $casts = [];
        foreach ($tableDetails->getColumns() as $column) {
            $model->addProperty(new VirtualPropertyModel(
                $column->getName(),
                $this->typeRegistry->resolveType($column->getType()->getName())
            ));

            if (!in_array($column->getName(), $skippingColumns)) {
                $fillable[] = $column->getName();
            }

            $casts[$column->getName()] = $this->resolveCast($column);
        }

        // Add fillable
        $fillableProperty = new PropertyModel('fillable');
        $fillableProperty->setAccess('protected')
            ->setValue($fillable)
            ->setDocBlock(new DocBlockModel('@var array'));
        $model->addProperty($fillableProperty);

        // Add casts
        $castsProperty = new PropertyModel('casts');
        $castsProperty->setAccess('protected')
            ->setValue($casts)
            ->setDocBlock(new DocBlockModel('@var array'));
        $model->addProperty($castsProperty);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return 5;
    }

    private function resolveCast(\Doctrine\DBAL\Schema\Column $column)
    {
        $type = $column->getType();

        $typeName = strtolower($type->getName());
        switch ($typeName) {
            case 'int':
            case 'bigint':
                return 'integer';
            case 'decimal':
                return 'decimal:' . $column->getScale();
            case 'json':
                return 'array';
            /**
             * Including:
             *  - real
             *  - float
             *  - double
             *  - string
             *  - boolean
             *  - date
             *  - datetime
             *  - timestamp
             */
            default:
                return $typeName;
        }
    }
}
