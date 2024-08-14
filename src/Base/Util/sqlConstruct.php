<?php

namespace Jenyus\Base\Util;

use InvalidArgumentException;
use PDO;

trait sqlConstruct
{

    public static function bindValue($values, $query)
    {
        // Asignar valores a los placeholders
        foreach ($values as $key => $value) {
            $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $query->bindValue($key, $value, $param_type);
        }
    }

    public static function bindParam($value, $query)
    {
        $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $query->bindParam(':value', $value, $param_type);
    }

    public static function insertSQL($columns, $table)
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

    public static function whereSQL($value, $columns = ['*'], $operator = '=', $column = 'id', $table)
    {
        // Convertir el array de columnas en una cadena separada por comas
        $columnsStr = implode(', ', $columns);

        // Construir la consulta preparada
        return "SELECT {$columnsStr} FROM {$table} WHERE {$column} {$operator} :value";
    }

    public function selectSQL($columns, $table){
        $columnsStr = implode(', ', $columns);
        return "SELECT {$columnsStr} FROM {$table}";
    }

    public function updateSQL($columns, $operator, $column, $table){
        
        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be associative array");
        }

        $sets = "";
        foreach ($columns as $column => $value) {
            $sets .= "$column = :$column, ";
        }

        $sets = rtrim($sets, ', ');

        return "UPDATE {$table} SET {$sets} WHERE $column $operator :value";
    }

    public function deleteSQL($column, $operator, $table){
        return "DELETE FROM {$table} WHERE $column $operator :value";
    }

}
