<?php

namespace Jenyus\Base;

use Jenyus\Base\Util\ModelFormat;
use PDO;

class DynamicModel
{
    use ModelFormat;

    private $conexion; // Objeto PDO para la conexión a la base de datos
    protected $query; // Objeto de consulta PDO
    protected $table; // Nombre de la tabla en la base de datos

    /**
     * Constructor de la clase DynamicModel.
     * 
     * @param PDO $conexion Objeto PDO para la conexión a la base de datos.
     */
    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Establece la tabla en la que se ejecutarán las consultas.
     * 
     * @param string $table Nombre de la tabla en la base de datos.
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Ejecuta una consulta SQL personalizada.
     * 
     * @param string $sql Consulta SQL a ejecutar.
     * @return $this
     */
    public function query($sql)
    {
        $this->query = $this->conexion->query($sql);
        return $this;
    }

    /**
     * Obtiene la primera fila de resultados de la consulta actual.
     * 
     * @return array|false Arreglo asociativo con los resultados de la consulta.
     */
    public function first()
    {
        return $this->query->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los resultados de la consulta actual.
     * 
     * @return array Arreglo de arreglos asociativos con los resultados de la consulta.
     */
    public function get()
    {
        return $this->query->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Ejecuta una consulta para obtener todos los registros de la tabla especificada.
     * 
     * @param array $columns Columnas a seleccionar (por defecto ['*']).
     * @return $this
     */
    public function all($columns = ['*'])
    {
        // Convertir el array de columnas en una cadena separada por comas
        $columnsStr = implode(', ', $columns);
        // Construir la consulta preparada
        $sql = "SELECT {$columnsStr} FROM {$this->table}";
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $this->query = $stmt;
            return $this;
        } catch (\PDOException $e) {
            return [false, $e->getMessage()];
        }
    }

    /**
     * Ejecuta una consulta con una cláusula WHERE.
     * 
     * @param string $column Columna para la condición WHERE.
     * @param mixed $value Valor a comparar.
     * @param string $operator Operador de comparación (por ejemplo, '=', '>', '<', etc.).
     * @param array $columns Columnas a seleccionar (por defecto ['*']).
     * @return array Arreglo con el resultado de la consulta.
     */
    public function where($column, $value, $operator, $columns = ['*'])
    {
        // Convertir el array de columnas en una cadena separada por comas
        $columnsStr = implode(', ', $columns);
        // Construir la consulta preparada
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE {$column} {$operator} :value";
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
            $stmt->execute();
            $this->query = $stmt;
            return [true, $this];
        } catch (\PDOException $e) {
            return [false, $e->getMessage()];
        }
    }

    /**
     * Busca un registro por su valor en una columna específica.
     * 
     * @param mixed $value Valor a buscar.
     * @param array $columns Columnas a seleccionar (por defecto ['*']).
     * @param string $operator Operador de comparación (por ejemplo, '=', '>', '<', etc.).
     * @param string $column Columna para la condición de búsqueda (por defecto 'id').
     * @return array Arreglo con el resultado de la consulta.
     */
    public function find($value, $columns = ['*'], $operator = '=', $column = 'id')
    {
        // Convertir el array de columnas en una cadena separada por comas
        $columnsStr = implode(', ', $columns);
        // Construir la consulta preparada
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE {$column} {$operator} :value";
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
            $stmt->execute();
            $this->query = $stmt;
            return [true, $this];
        } catch (\PDOException $e) {
            return [false, $e->getMessage()];
        }
    }

    /**
     * Inserta nuevos registros en la tabla especificada.
     * 
     * @param array $columns array asociativo con las columnas y valores
     * @return array Arreglo con el resultado de la inserción.
     */
    public function insert($columns = [], $dateTime = true)
    {
        // Añadir 'created_at' a las columnas y su valor correspondiente a los valores
        if($dateTime){
            $columns['created_at'] = $this->basicCurrentFormatDate();
        }
        $columnNames = '';
        $placeholders = '';
        $values = [];

        foreach ($columns as $key => $value) {
            $columnNames .= $key . ', ';
            $placeholders .= ':' . $key . ', ';
            $values[':' . $key] = $value;
        }

        // Eliminar las comas y espacios al final de las cadenas
        $columnNames = rtrim($columnNames, ', ');
        $placeholders = rtrim($placeholders, ', ');

        $sql = "INSERT INTO {$this->table} ({$columnNames}) VALUES ({$placeholders})";
        try {
            $stmt = $this->conexion->prepare($sql);
            // Asignar valores a los placeholders
            foreach ($values as $key => $value) {
                $param_type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $param_type);
            }
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $id = $this->conexion->lastInsertId();
                return [true, $id];
            }
            // Si no se insertó ninguna fila (rowCount <= 0), puede manejarlo según tus requerimientos.
            return [false, 'No se insertaron filas'];
        } catch (\PDOException $e) {
            die($$e->getMessage());
            // Manejar errores de PDO
            return [false, $e->getMessage()];
        }
    }


    /**
     * Actualiza registros en la tabla especificada.
     * 
     * @param array $columns Columnas a actualizar.
     * @param mixed $value Valor de la condición WHERE.
     * @param string $operator Operador de comparación (por ejemplo, '=', '>', '<', etc.).
     * @param string $column Columna para la condición WHERE (por defecto 'id').
     * @return array Arreglo con el resultado de la actualización.
     */
    public function update($columns = [], $value, $operator = '=', $column = 'id')
    {
        // Construir la cadena SET
        $sets = "";
        foreach ($columns as $key => $c) {
            $sets .= "$key = :$key, ";
        }
        $sets = rtrim($sets, ', ');
        $sql = "UPDATE {$this->table} SET {$sets} WHERE $column $operator :whereValue";

        try {
            $stmt = $this->conexion->prepare($sql);
            // Enlazar los valores de las columnas
            foreach ($columns as $key => $val) {
                $param_type = is_int($val) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue(":$key", $val, $param_type);
            }
            // Enlazar el valor para la cláusula WHERE
            $param_type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindValue(":whereValue", $value, $param_type);
            $stmt->execute();
            return [$stmt->rowCount() > 0, ''];
        } catch (\PDOException $e) {
            return [false, $e->getMessage()];
        }
    }

    /**
     * Elimina registros de la tabla especificada.
     * 
     * @param mixed $value Valor de la condición WHERE.
     * @param string $operator Operador de comparación (por ejemplo, '=', '>', '<', etc.).
     * @param string $column Columna para la condición WHERE (por defecto 'id').
     * @return array Arreglo con el resultado de la eliminación.
     */
    public function delete($value, $operator = '=', $column = 'id')
    {
        $sql = "DELETE FROM {$this->table} WHERE {$column} {$operator} :value";
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
            $stmt->execute();
            return [$stmt->rowCount() > 0, ''];
        } catch (\PDOException $e) {
            return [false, $e->getMessage()];
        }
    }
}
