<?php

namespace Jenyus\Base\Util;

use InvalidArgumentException;
use PDO;
use PDOException;

trait sqlConstruct
{

    private $where = [];
    private $values = [];
    private  $columns = '';

    public function bindValue($values, $query)
    {
        // Asignar valores a los placeholders
        foreach ($values as $key => $value) {
            $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $query->bindValue($key, $value, $param_type);
        }
    }

    public function bindValue2($values, $query)
    {
        foreach ($values as $key => $value) {
            $placeholder = ":value{$key}"; // Generar placeholders dinámicos como :value0, :value1, etc.
            $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $query->bindValue($placeholder, $value, $param_type);
        }
    }


    public function bindParam($value, $query)
    {
        $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $query->bindParam(':value', $value, $param_type);
    }

    public function insertSQL($columns, $table)
    {
        $columnNames = '';
        $placeholders = '';

        foreach ($columns as $key => $value) {
            $columnNames .= $key . ', ';
            $placeholders .= ':' . $key . ', ';
        }

        // Eliminar las comas y espacios al final de las cadenas
        $columnNames = rtrim($columnNames, ', ');
        $placeholders = rtrim($placeholders, ', ');

        return "INSERT INTO {$table} ({$columnNames}) VALUES ({$placeholders})";
    }

    public function whereSQL($column = 'id', $operator = '=', $value, $columns = ['*'])
    {
        $columnsStr = implode(', ', $columns);

        $placeholder = ":value" . count($this->values);

        if ($this->where) {
            $this->where[] = " AND {$column} {$operator} {$placeholder}";
        } else {
            $this->columns = $columnsStr;
            $this->where[] = "WHERE {$column} {$operator} {$placeholder}";
        }

        $whereStr = implode(' ', $this->where);

        $this->values[] = $value;

        $sql = "SELECT {$this->columns} FROM {$this->table} {$whereStr}";

        $this->prepare($sql);

        $this->bindValue2($this->values, $this->query);
    }

    public function orWhereSQL($column = 'id', $operator = '=', $value)
    {

        $placeholder = ":value" . count($this->values);

        if ($this->where) {
            $this->where[] = " OR {$column} {$operator} {$placeholder}";
        } else {
            throw new PDOException("Llama al metodo where antes del metodo orWhere", 400);
        }

        $whereStr = implode(' ', $this->where);

        $this->values[] = $value;

        $sql = "SELECT {$this->columns} FROM {$this->table} {$whereStr}";

        $this->prepare($sql);

        $this->bindValue2($this->values, $this->query);
    }


    public function selectSQL($columns, $table)
    {
        $columnsStr = implode(', ', $columns);
        return "SELECT {$columnsStr} FROM {$table}";
    }

    public function updateSQL($columns, $operator, $column, $table)
    {

        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be associative array");
        }

        $sets = "";
        foreach ($columns as $key => $value) {
            $sets .= "$key = :$key, ";
        }

        $sets = rtrim($sets, ', ');

        return "UPDATE {$table} SET {$sets} WHERE $column $operator :value";
    }

    public function deleteSQL($column, $operator, $table)
    {
        return "DELETE FROM {$table} WHERE $column $operator :value";
    }
}
