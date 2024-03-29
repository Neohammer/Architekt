<?php

namespace Architekt\DB\Languages\MySQL;

use Architekt\DB\DBRecordColumn;
use Architekt\DB\DBRecordRow;
use Architekt\DB\DBRecordRowFilter;
use Architekt\DB\Exceptions\MissingConfigurationException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

trait MySQLRecordFilterTrait
{
    private function buildFilters(array|string|DBRecordRow $DBRecordRows, bool $useDatatable = false): void
    {
        if (!is_array($DBRecordRows)) {
            $DBRecordRows = [$DBRecordRows];
        }

        $operatorSkip = false;
        foreach ($DBRecordRows as $DBRecordRow) {

            if(is_string($DBRecordRow)){
                $this->filters[] = $DBRecordRow;
                $operatorSkip = true;
                continue;
            }
            foreach ($DBRecordRow->filters() as $filter) {
                $filterText = '';
                if (sizeof($this->filters) > 0) {
                    if(!$operatorSkip){
                        if ($filter->type() === DBRecordRowFilter::TYPE_AND) {
                            $filterText.= 'AND ';
                        } elseif ($filter->type() === DBRecordRowFilter::TYPE_OR) {
                            $filterText.= 'OR ';
                        } else {
                            throw new MissingOptionsException(sprintf('MysqlRecordDelete does not support Filter with %s type', $filter->type()));
                        }
                    }
                    $operatorSkip = false;
                } else {
                    $filterText = ' WHERE ';
                }

                if ($useDatatable) {
                    $filterText .= sprintf(
                        '%s.%s',
                        self::quote($DBRecordRow->datatable()),
                        self::quote($filter->key())
                    );
                } else {
                    $filterText .= self::quote($filter->key());
                }
                if ($filter->value() === null) {
                    $filterText .= ' IS';
                    if (!$filter->affirmative()) {
                        $filterText .= ' NOT';
                    }
                    $filterText .= ' NULL';
                } elseif ($filter->egalityType() === DBRecordRowFilter::EGALITY_BETWEEN) {
                    if (!$filter->affirmative()) {
                        $filterText .= ' NOT';
                    }
                    $filterText .= sprintf(
                        ' BETWEEN %s AND %s',
                        $key1 = self::prepareFormat($filter->key().'0'),
                        $key2 = self::prepareFormat($filter->key().'1')
                    );

                    $this->params[$key1] = $filter->value()[0];
                    $this->params[$key2] = $filter->value()[1];
                } elseif ($filter->egalityType() === DBRecordRowFilter::EGALITY_CONTAINS) {
                    if (!$filter->affirmative()) {
                        $filterText .= ' NOT';
                    }
                    $filterText .= sprintf(' LIKE "%%%s%%"', $filter->value());
                } else {
                    if ($filter->egalityType() === DBRecordRowFilter::EGALITY_EQUAL) {
                        if (!$filter->affirmative()) {
                            $filterText .= "!";
                        }
                        $filterText .= "=";
                    }

                    if (in_array($filter->egalityType(), [DBRecordRowFilter::EGALITY_GREATER, DBRecordRowFilter::EGALITY_GREATER_OR_EQUAL])) {
                        if ($filter->affirmative()) {
                            $filterText .= ">";
                        } else {
                            $filterText .= "<";
                        }
                    }
                    if ($filter->egalityType() === DBRecordRowFilter::EGALITY_GREATER_OR_EQUAL) {
                        $filterText .= "=";
                    }

                    if ($filter->value() instanceof DBRecordColumn) {
                        if ($subName = $filter->value()->name()) {
                            if ($useDatatable) {
                                $filterText .= sprintf(
                                    '%s.%s',
                                    self::quote($filter->value()->datatable()),
                                    self::quote($subName)
                                );
                            } else {
                                $filterText .= self::quote($subName);
                            }
                        } else {
                            throw new MissingConfigurationException('Filtering on subfield with multiple filters is not supported yet');
                        }
                    } else {
                        $filterText .= ($key = self::prepareFormat($filter->key()));
                        $this->params[$key] = $filter->value();
                    }
                }
                $this->filters[] = $filterText;
            }
        }
    }

    private function buildLeftFilters(array|DBRecordRow $DBRecordRows, bool $useDatatable = false): void
    {

        if (!is_array($DBRecordRows)) {
            $DBRecordRows = [$DBRecordRows];
        }

        foreach ($DBRecordRows as $DBRecordRow) {
            foreach ($DBRecordRow->filters() as $filter) {
                if (sizeof($this->leftFilters) > 0) {
                    if ($filter->type() === DBRecordRowFilter::TYPE_AND) {
                        $filterText = 'AND ';
                    } elseif ($filter->type() === DBRecordRowFilter::TYPE_OR) {
                        $filterText = 'OR ';
                    } else {
                        throw new MissingOptionsException(sprintf('MysqlRecordDelete does not support Filter with %s type', $filter->type()));
                    }
                } else {
                    $filterText = ' ON ';
                }

                if ($useDatatable) {
                    $filterText .= sprintf(
                        '%s.%s',
                        self::quote($DBRecordRow->datatable()),
                        self::quote($filter->key())
                    );
                } else {
                    $filterText .= self::quote($filter->key());
                }

                if ($filter->value() === null) {
                    $filterText .= ' IS';
                    if (!$filter->affirmative()) {
                        $filterText .= ' NOT';
                    }
                    $filterText .= ' NULL';
                } elseif ($filter->egalityType() === DBRecordRowFilter::EGALITY_CONTAINS) {
                    if (!$filter->affirmative()) {
                        $filterText .= ' NOT';
                    }
                    $filterText .= sprintf(' LIKE "%%%s%%"', $filter->value());
                } else {
                    if ($filter->egalityType() === DBRecordRowFilter::EGALITY_EQUAL) {
                        if (!$filter->affirmative()) {
                            $filterText .= "!";
                        }
                        $filterText .= "=";
                    }

                    if (in_array($filter->egalityType(), [DBRecordRowFilter::EGALITY_GREATER, DBRecordRowFilter::EGALITY_GREATER_OR_EQUAL])) {
                        if ($filter->affirmative()) {
                            $filterText .= ">";
                        } else {
                            $filterText .= "<";
                        }
                    }
                    if ($filter->egalityType() === DBRecordRowFilter::EGALITY_GREATER_OR_EQUAL) {
                        $filterText .= "=";
                    }

                    if ($filter->value() instanceof DBRecordColumn) {
                        if ($subName = $filter->value()->name()) {
                            if ($useDatatable) {
                                $filterText .= sprintf(
                                    '%s.%s',
                                    self::quote($filter->value()->datatable()),
                                    self::quote($subName)
                                );
                            } else {
                                $filterText .= self::quote($subName);
                            }
                        } else {
                            throw new MissingConfigurationException('Filtering on subfield with multiple filters is not supported yet');
                        }
                    } else {
                        $filterText .= ($key = self::prepareFormat($filter->key()));
                        $this->params[$key] = $filter->value();
                    }
                }
                $this->leftFilters[] = $filterText;
            }
        }
    }

    private function buildInnerFilters(array|DBRecordRow $DBRecordRows, bool $useDatatable = false): void
    {
        if (!is_array($DBRecordRows)) {
            $DBRecordRows = [$DBRecordRows];
        }

        foreach ($DBRecordRows as $DBRecordRow) {
            foreach ($DBRecordRow->filters() as $filter) {
                if (sizeof($this->innerFilters) > 0) {
                    if ($filter->type() === DBRecordRowFilter::TYPE_AND) {
                        $filterText = ' AND ';
                    } elseif ($filter->type() === DBRecordRowFilter::TYPE_OR) {
                        $filterText = ' OR ';
                    } else {
                        throw new MissingOptionsException(sprintf('MysqlRecordDelete does not support Filter with %s type', $filter->type()));
                    }
                } else {
                    $filterText = ' ON ';
                }

                if ($useDatatable) {
                    $filterText .= sprintf(
                        '%s.%s',
                        self::quote($DBRecordRow->datatable()),
                        self::quote($filter->key())
                    );
                } else {
                    $filterText .= self::quote($filter->key());
                }

                if ($filter->value() === null) {
                    $filterText .= ' IS';
                    if (!$filter->affirmative()) {
                        $filterText .= ' NOT';
                    }
                    $filterText .= ' NULL';
                } elseif ($filter->egalityType() === DBRecordRowFilter::EGALITY_CONTAINS) {
                    if (!$filter->affirmative()) {
                        $filterText .= ' NOT';
                    }
                    $filterText .= sprintf(' LIKE "%%%s%%"', $filter->value());
                } else {
                    if ($filter->egalityType() === DBRecordRowFilter::EGALITY_EQUAL) {
                        if (!$filter->affirmative()) {
                            $filterText .= "!";
                        }
                        $filterText .= "=";
                    }

                    if (in_array($filter->egalityType(), [DBRecordRowFilter::EGALITY_GREATER, DBRecordRowFilter::EGALITY_GREATER_OR_EQUAL])) {
                        if ($filter->affirmative()) {
                            $filterText .= ">";
                        } else {
                            $filterText .= "<";
                        }
                    }
                    if ($filter->egalityType() === DBRecordRowFilter::EGALITY_GREATER_OR_EQUAL) {
                        $filterText .= "=";
                    }

                    if ($filter->value() instanceof DBRecordColumn) {
                        if ($subName = $filter->value()->name()) {
                            if ($useDatatable) {
                                $filterText .= sprintf(
                                    '%s.%s',
                                    self::quote($filter->value()->datatable()),
                                    self::quote($subName)
                                );
                            } else {
                                $filterText .= self::quote($subName);
                            }
                        } else {
                            throw new MissingConfigurationException('Filtering on subfield with multiple filters is not supported yet');
                        }
                    } else {
                        $filterText .= ($key = self::prepareFormat($filter->key()));
                        $this->params[$key] = $filter->value();
                    }
                }
                $this->innerFilters[] = $filterText;
            }
        }
    }


    public function filterAnd(): static
    {
        $this->filterRows[] = 'AND (';

        return $this;
    }

    public function filterOr(): static
    {
        $this->filterRows[] = 'OR (';

        return $this;
    }

    public function filterEnd(): static
    {
        $this->filterRows[] = ')';

        return $this;
    }
}