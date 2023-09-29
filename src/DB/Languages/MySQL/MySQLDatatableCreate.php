<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\Abstraction\Query;
use Architekt\DB\DBDatatableColumn;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Architekt\DB\Interfaces\DBDatatableInterface;
use Architekt\DB\Interfaces\DBQueryBuilderInterface;

class MySQLDatatableCreate extends MySQLTools implements DBQueryBuilderInterface
{
    private DBDatatableInterface $datatable;
    private array $fields;
    private array $indexes;

    public function __construct(DBDatatableInterface $datatable)
    {
        $this->datatable = $datatable;
        $this->fields = [];
        $this->indexes = [];
    }

    public function query(): Query
    {
        return new Query(
            sprintf(
                "CREATE TABLE IF NOT EXISTS %s (%s) ENGINE=InnoDb",
                self::quote($this->datatable->name()),
                $this->parseColumns()
            )
        );
    }

    private function parseColumns(): string
    {
        foreach ($this->datatable->columns() as $column) {

            switch ($column->type()) {
                case DBDatatableColumn::TYPE_NUMERIC:
                    $field = [
                        self::quote($column->name()),
                        'INT',
                        $column->nullable() ? 'NULL' : 'NOT NULL'
                    ];

                    if ($column->autoincrement()) {
                        $field[] = 'AUTO_INCREMENT';
                    }

                    break;
                case DBDatatableColumn::TYPE_STRING:
                    $field = [
                        self::quote($column->name()),
                        $column->multiLines() ? 'TEXT' : sprintf('VARCHAR(%s)', $column->length()),
                        $column->nullable() ? 'NULL' : 'NOT NULL'
                    ];

                    if ($column->hasDefault()) {
                        if ($column->default() === null) {
                            $default = 'NULL';
                        } else {
                            $default = sprintf('\'%s\'', $column->default());
                        }
                        $field[] = sprintf(
                            'DEFAULT %s',
                            $default
                        );
                    }

                    break;
                case DBDatatableColumn::TYPE_BOOLEAN:
                    $field = [
                        self::quote($column->name()),
                        'TINYINT(1)',
                        'UNSIGNED',
                        $column->nullable() ? 'NULL' : 'NOT NULL'
                    ];

                    if ($column->hasDefault()) {
                        $default = $column->default() ? 1 : 0;
                        if ($column->default() === null) {
                            $default = 'NULL';
                        }

                        $field[] = sprintf(
                            'DEFAULT %s',
                            $default
                        );
                    }
                    break;
                default:
                    throw new MissingConfigurationException(sprintf('MySQL does not support %s column type on datatable creation', $column->type()));
            }

            $this->fields[] = join(' ', $field);

            if ($column->primary()) {
                $this->indexes[] = sprintf(
                    'PRIMARY KEY (%s)',
                    self::quote($column->name())
                );
            }

        }
        return join(', ', array_merge($this->fields, $this->indexes));
    }
}