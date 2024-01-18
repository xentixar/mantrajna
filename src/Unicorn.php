<?php

namespace Xentixar\MantrajnaTest;

use DateTimeImmutable;
use Error;
use TypeError;

class Column
{
    public $column_name;
    public $column_type;
    public $column_format;

    public function __construct(string $column_name, string $column_type = "string", string $column_format = null)
    {
        $this->column_name = $column_name;
        $this->column_type = $column_type;
        $this->column_format = $column_format;
    }
}

class Condition
{
    public $column_index;
    public $operator;
    public $value;

    public function __construct(int|null $column_index, string $operator, string $value)
    {
        $this->column_index = $column_index;
        $this->operator = $operator;
        $this->value = $value;
    }
}

class Unicorn
{
    protected $initial_data;
    protected $modified_data;
    protected $final_data;
    protected $columns;
    protected $selected_columns;
    protected $total_row_count;
    protected $available_column_types;
    protected $conditions;
    protected $condition_type;
    protected $applied_format;

    public function __construct()
    {
        $this->initial_data = [];
        $this->final_data = [];
        $this->columns = [];
        $this->selected_columns = [];
        $this->total_row_count = 0;
        $this->available_column_types = ['datetime', 'string', 'int', 'double'];
        $this->conditions = [];
        $this->condition_type = "and";
        $this->applied_format = false;
    }

    private function __clearData(): void
    {
        $this->initial_data = [];
        $this->final_data = [];
        $this->columns = [];
        $this->selected_columns = [];
        $this->total_row_count = 0;
        $this->available_column_types = ['datetime', 'string', 'int', 'double'];
        $this->conditions = [];
        $this->condition_type = "and";
        $this->applied_format = false;
    }

    private function __setDefaultColumnInfo(array $temp_columns): void
    {
        foreach ($temp_columns as $column_index => $column_name) {
            $this->columns[$column_index] = new Column($column_name);
        }
    }

    private function __checkCondition(): void
    {
        if (!$this->modified_data && $this->applied_format === false) {
            $this->modified_data = $this->initial_data;
        }

        $temp_data = [];

        foreach ($this->modified_data as $row_key => $data) {
            $count = 0;
            foreach ($this->conditions as $condition) {
                $value = $data[$condition->column_index];
                if ($this->columns[$condition->column_index]->column_type === 'datetime') {
                    $value = (array)$value;
                    $value = date('Y-m-d', strtotime($value['date']));
                }
                $operator = $condition->operator;
                switch ($operator) {
                    case '=':
                        if ($value == $condition->value) {
                            $count++;
                        }
                        break;
                    case '!=':
                        if ($value != $condition->value) {
                            $count++;
                        }
                        break;
                    case '<':
                        if ($value < $condition->value) {
                            $count++;
                        }
                        break;
                    case '>':
                        if ($value > $condition->value) {
                            $count++;
                        }
                        break;
                    case '<=':
                        if ($value <= $condition->value) {
                            $count++;
                        }
                        break;
                    case '>=':
                        if ($value >= $condition->value) {
                            $count++;
                        }
                        break;
                    default:
                        throw new Error("Operator $operator not found!");
                }
            }

            if ($this->condition_type === 'and') {
                if ($count === count($this->conditions)) {
                    $temp_data[] = $data;
                }
            } elseif ($this->condition_type === 'or') {
                if ($count === 1) {
                    $temp_data[] = $data;
                }
            }
        }
        $this->modified_data = $temp_data;
        $this->applied_format = true;
    }

    public function readCsv(string $file, string $delimeter = ',')
    {
        $this->__clearData();
        if (pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
            $temp_data = [];
            if (($handle = fopen($file, 'r')) !== false) {
                while (($fetchedData = fgetcsv($handle, null, $delimeter)) !== false) {
                    if ($this->total_row_count !== 0) {
                        $temp_data[] = $fetchedData;
                    } else {
                        $temp_columns = $fetchedData;
                    }
                    $this->total_row_count++;
                }
                fclose($handle);
            }
            $this->initial_data = $temp_data;
        } else {
            throw new Error('Expected file type csv, ' . pathinfo($file, PATHINFO_EXTENSION) . " given!");
        }
        $this->__setDefaultColumnInfo($temp_columns);
    }

    public function sort(string $column_name, string $order = 'ASC'): self
    {
        $found = false;
        $found_column_index = null;
        foreach ($this->columns as $key => $column) {
            if ($column->column_name === $column_name) {
                $found = true;
                $found_column_index = $key;
            }
        }
        if (!$found) {
            throw new Error("Column $column->column_name not found");
        }

        if (!$this->modified_data && $this->applied_format === false) {
            $this->modified_data = $this->initial_data;
        }
        $names = array_column($this->modified_data, $found_column_index);
        if ($order !== 'DESC') {
            $order = SORT_ASC;
        } else {
            $order = SORT_DESC;
        }
        array_multisort($names, $order, $this->modified_data);
        $this->applied_format = true;
        return $this;
    }

    public function setColumnInfo(...$column_info): self
    {
        foreach ($column_info as $info) {
            $found = false;
            $found_column_index = null;
            $exploded_info = explode(':', $info);
            if (!isset($exploded_info[1])) {
                $exploded_info[1] = "string";
            }
            if (array_search($exploded_info[1], $this->available_column_types) === false) {
                throw new Error("Type $exploded_info[1] not availabe");
            }
            if ($exploded_info[1] === 'datetime' && !isset($exploded_info[2])) {
                throw new Error('Third parameter (format) missing for datetime type');
            }
            foreach ($this->columns as $key => $column) {
                if ($column->column_name === $exploded_info[0]) {
                    $found = true;
                    $found_column_index = $key;
                    break;
                }
            }
            if (!$found) {
                throw new Error("Column $exploded_info[0] not found");
            }

            if (!$this->modified_data && $this->applied_format === false) {
                $this->modified_data = $this->initial_data;
            }

            foreach ($this->modified_data as $row_key => $data) {
                foreach ($data as $column_key => $value) {
                    if ($column_key === $found_column_index) {
                        if ($exploded_info[1] === 'datetime') {
                            $updated_value = DateTimeImmutable::createFromFormat($exploded_info[2], $value);
                            if ($updated_value === false) {
                                $column_name = $this->columns[$found_column_index]->column_name;
                                throw new TypeError("Column $column_name can't be converted to datetime");
                            }
                            $this->modified_data[$row_key][$column_key] = $updated_value;
                            $this->columns[$column_key]->column_type = $exploded_info[1];
                        } elseif ($exploded_info[1] === 'int') {
                            $updated_value = (int)$value;
                            $this->modified_data[$row_key][$column_key] = $updated_value;
                            $this->columns[$column_key]->column_type = $exploded_info[1];
                        } elseif ($exploded_info[1] === 'double') {
                            $updated_value = (float)$value;
                            $this->modified_data[$row_key][$column_key] = $updated_value;
                            $this->columns[$column_key]->column_type = $exploded_info[1];
                        } elseif ($exploded_info[1] === 'string') {
                            $updated_value = (string)$value;
                            $this->modified_data[$row_key][$column_key] = $updated_value;
                            $this->columns[$column_key]->column_type = $exploded_info[1];
                        }
                    }
                }
            }
        }
        $this->applied_format = true;
        return $this;
    }

    public function where(...$conditions): self
    {
        foreach ($conditions as $condition) {
            if (!is_array($condition)) {
                throw new Error('Condition type array expected');
            }
            if (count($condition) !== 3) {
                throw new Error('Only 3 parameters accepted');
            }

            $found = false;
            $found_column_index = null;
            foreach ($this->columns as $key => $column) {
                if ($column->column_name === $condition[0]) {
                    $found = true;
                    $found_column_index = $key;
                    break;
                }
            }
            if (!$found) {
                throw new Error("Column $condition[0] not found");
            }
            $this->conditions[] = new Condition($found_column_index, $condition[1], $condition[2]);
        }
        $this->__checkCondition();
        return $this;
    }

    public function selectColumns(...$columns): self
    {
        if (!$this->modified_data && $this->applied_format === false) {
            $this->modified_data = $this->initial_data;
        }
        $found_column_index = null;
        $temp_data = [];
        foreach ($this->modified_data as $row_key => $data) {
            foreach ($columns as $column) {
                $found = false;
                foreach ($this->columns as $key => $col) {
                    if ($col->column_name === $column) {
                        $found = true;
                        $found_column_index = $key;
                        $this->selected_columns[] = $col->column_name;
                        break;
                    }
                }
                if (!$found) {
                    throw new Error("Column $column not found");
                } else {
                    $temp_data[$row_key][] = $data[$found_column_index];
                }
            }
        }

        $this->modified_data = $temp_data;
        $this->applied_format = true;
        return $this;
    }

    public function format(...$columns): self
    {
        if (!$this->modified_data && $this->applied_format === false) {
            $this->modified_data = $this->initial_data;
        }

        foreach ($columns as $column) {
            $found = false;
            $found_column_index = null;
            $exploded_data = explode(':', $column);
            foreach ($this->columns as $key => $column) {
                if ($column->column_name === $exploded_data[0]) {
                    $found = true;
                    $found_column_index = $key;
                    break;
                }
            }
            if (!$found) {
                throw new Error("Column $column->column_name not found");
            }

            foreach ($this->modified_data as $row_key => $row) {
                foreach ($row as $column_key => $value) {
                    if ($this->columns[$column_key]->column_type === 'datetime') {
                        if (!isset($exploded_data[1])) {
                            $format = "Y-m-d";
                        } else {
                            $format = $exploded_data[1];
                        }
                        $value = (array) $this->modified_data[$row_key][$column_key];
                        $this->modified_data[$row_key][$column_key] = date($format, strtotime($value['date']));
                    }
                }
            }
        }
        $this->applied_format = true;
        return $this;
    }

    public function any(): self
    {
        $this->condition_type = 'or';
        return $this;
    }

    public function all(): self
    {
        $this->condition_type = 'and';
        return $this;
    }

    public function run(): self
    {
        if (!$this->modified_data && $this->applied_format === false) {
            $this->final_data = $this->initial_data;
        } else {
            $this->final_data = $this->modified_data;
        }
        return $this;
    }

    public function clearAllFormat(): void
    {
        $this->modified_data = [];
        $this->final_data = [];
        $this->conditions = [];
        $this->condition_type = 'and';
        $this->applied_format = false;
        $this->selected_columns = [];

        foreach ($this->columns as $column_key => $column) {
            $this->columns[$column_key]->column_type = "string";
            $this->columns[$column_key]->column_format = null;
        }
    }

    public function get(int $row_count = 10): array
    {
        if ($row_count < 0) {
            $row_count = $this->total_row_count;
        }

        $final_data = [];

        for ($i = 0; $i < $row_count && isset($this->final_data[$i]); $i++) {
            foreach ($this->final_data[$i] as $column_key => $value) {
                $column_name = $this->columns[$column_key]->column_name;
                $final_data[$i][$column_name] = $value;
            }
        }
        return $final_data;
    }

    public function print(int $row_count = 10): void
    {
        if ($row_count < 0) {
            $row_count = $this->total_row_count;
        }

        foreach ($this->columns as $column) {
            if ($this->selected_columns) {
                if (array_search($column->column_name, $this->selected_columns) !== false) {
                    printf("%-30s", $column->column_name);
                }
            } else {
                printf("%-30s", $column->column_name);
            }
        }

        echo PHP_EOL;
        for ($i = 0; $i < $row_count && isset($this->final_data[$i]); $i++) {
            foreach ($this->final_data[$i] as $column_key => $value) {
                if ($this->columns[$column_key]->column_type === 'datetime' && is_object($value)) {
                    $value = (array)$value;
                    $value = date('Y-m-d', strtotime($value['date']));
                }
                printf("%-30s", $value);
            }
            echo PHP_EOL;
        }
    }
}
