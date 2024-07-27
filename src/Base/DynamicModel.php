<?php

namespace Jenyus\Base;

use Exception;
use InvalidArgumentException;
use Jenyus\Base\Util\ModelFormat;
use PDO;
use PDOException;

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
     * @param $table Puedes definir opcionalmemte la propiedad table, al iniciar tu objeto.
     * @throws PDOException Si no se proporciona una instancia válida de PDO.
     */
    public function __construct(PDO $conexion, $table = null)
    {
        if (!$conexion instanceof PDO) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: Se requiere una instancia válida de PDO.", 422);
        }
        if ($table != null) {
            $this->SetTable($table);
        }
        $this->conexion = $conexion;
    }

    /**
     * Establece la tabla en la que se ejecutarán las consultas.
     * 
     * @param string $table Nombre de la tabla en la base de datos.
     * @return $this
     */
    public function SetTable($table)
    {
        if (!is_string($table)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: 'table' property must be a string. Please provide a valid table name.", 422);
        }
        $this->table = $table;
        return $this;
    }

    /**
     * @return $this->table value del objeto actual
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     */
    public function getTable()
    {
        if (!$this->table || empty($this->table) || $this->table == '') {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: Property 'table' cannot be null. Please call the 'table()' method with a valid table name before performing queries.", 422);
        }
        return $this->table;
    }

    /**
     * Ejecuta una consulta SQL personalizada.
     * 
     * @param string $sql Consulta SQL a ejecutar.
     * @return $this
     * @throws PDOException Si ocurre un error con la base de datos.
     */
    public function query($sql)
    {
        try {
            $this->query = $this->conexion->query($sql);
            return $this;
        } catch (PDOException $e) {
            throw new PDOException('Error in Jenyus\Base\DynamicModel: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Obtiene la primera fila de resultados de la consulta actual.
     * 
     * @return array|false Arreglo asociativo con los resultados de la consulta.
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.
     */
    public function first()
    {
        try {
            if (!$this->query) {
                throw new \PDOException("Error in Jenyus\Base\DynamicModel: No se ha ejecutado ninguna consulta previamente.");
            }
            $result = $this->query->fetch(\PDO::FETCH_ASSOC);
            if (!$result) {
                return false;
            }
            return $result;
        } catch (PDOException $e) {
            throw new PDOException('Error in Jenyus\Base\DynamicModel: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los resultados de la consulta actual.
     * 
     * @return array Arreglo de arreglos asociativos con los resultados de la consulta.
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.
     */
    public function get()
    {
        try {
            if (!$this->query) {
                throw new \PDOException("No se ha ejecutado ninguna consulta previamente.");
            }
            $result = $this->query->fetchAll(\PDO::FETCH_ASSOC);
            if (!$result) {
                return false;
            }
            return $result;
        } catch (PDOException $e) {
            throw new PDOException('Error in Jenyus\Base\DynamicModel: ' . $e->getMessage());
        }
    }

    /**
     * Ejecuta una consulta para obtener todos los registros de la tabla especificada.
     * 
     * @param array $columns Columnas a seleccionar (por defecto ['*']).
     * @return $this
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.ror interno.
     */
    public function all($columns = ['*'])
    {

        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be array", 422);
        }

        $this->getTable();
        // Convertir el array de columnas en una cadena separada por comas
        $columnsStr = implode(', ', $columns);
        // Construir la consulta preparada
        $sql = "SELECT {$columnsStr} FROM {$this->table}";
        try {
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute();
            $this->query = $stmt;
            return $this->get();
        } catch (PDOException $e) {
            throw new PDOException('Error in Jenyus\Base\DynamicModel: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Ejecuta una consulta con una cláusula WHERE.
     * 
     * @param string $column Columna para la condición WHERE.
     * @param mixed $value Valor a comparar.
     * @param string $operator Operador de comparación (por ejemplo, '=', '>', '<', etc.).
     * @return $this
     * @param array $columns Columnas a seleccionar (por defecto ['*']).
     * @return array Arreglo con el resultado de la consulta.
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.
     * @throws Exception Si ocurre un error interno.
     */
    public function where($column, $value, $operator = "=", $columns = ['*'])
    {
        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be array", 422);
        }

        $this->getTable();
        // Convertir el array de columnas en una cadena separada por comas
        $columnsStr = implode(', ', $columns);
        // Construir la consulta preparada
        $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE {$column} {$operator} :value";
        try {
            $stmt = $this->conexion->prepare($sql);
            $param_type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindParam(':value', $value, $param_type);
            $stmt->execute();
            $this->query = $stmt;
            return $this;
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument is not correct", 422);
        } catch (PDOException $e) {
            throw new PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage());
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
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.
     * @throws Exception Si ocurre un error interno.
     */
    public function find($value, $columns = ['*'], $operator = '=', $column = 'id')
    {
        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be array", 422);
        }

        $this->getTable();

        try {
            // Convertir el array de columnas en una cadena separada por comas
            $columnsStr = implode(', ', $columns);
            // Construir la consulta preparada
            $sql = "SELECT {$columnsStr} FROM {$this->table} WHERE {$column} {$operator} :value";
            $stmt = $this->conexion->prepare($sql);
            $param_type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindParam(':value', $value, $param_type);
            $stmt->execute();
            $this->query = $stmt;
            return $this->first();
        } catch (PDOException $e) {
            throw new PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        } catch (Exception $e) {
            throw new PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        }
    }

    /**
     * Inserta nuevos registros en la tabla especificada.
     * 
     * @param array $columns array asociativo con las columnas y valores
     * @return int id del registro insertado.
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.
     * @throws Exception Si ocurre un error interno.
     */
    public function insert($columns = [], $dateTime = true)
    {
        $this->getTable();
        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be associative array", 422);
        }

        try {
            // Añadir 'created_at' a las columnas y su valor correspondiente a los valores
            if ($dateTime) {
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
            $stmt = $this->conexion->prepare($sql);
            // Asignar valores a los placeholders
            foreach ($values as $key => $value) {
                $param_type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $param_type);
            }
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->conexion->lastInsertId();
            }

            throw new \RuntimeException('Jenyus\Base\DynamicModel: No se insertaron filas', 204);
        } catch (PDOException $e) {
            throw new PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        } catch (\Exception $e) {
            throw new Exception("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        }
    }


    /**
     * Actualiza registros en la tabla especificada.
     * 
     * @param array $columns Array asociativo con las columnas y sus nuevos valores.
     * @param mixed $value Valor de la condición WHERE.
     * @param string $operator Operador de comparación (por defecto '=').
     * @param string $column Columna para la condición WHERE (por defecto 'id').
     * @return int id del registro actualizado
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.
     * @throws Exception Si ocurre un error interno.
     */
    public function update($columns = [], $value, $operator = '=', $column = 'id')
    {
        $user = $this->find($value, ['id'], $operator, $column);
        if (!$user) {
            throw new PDOException("Error in Jenyus\Base\DynamicModel: el recurso no existe", 404);
        }
        $this->getTable();
        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be associative array");
        }

        try {
            // Construir la cadena SET
            $sets = "";
            foreach ($columns as $key => $c) {
                $sets .= "$key = :$key, ";
            }
            $sets = rtrim($sets, ', ');

            $sql = "UPDATE {$this->table} SET {$sets} WHERE $column $operator :whereValue";
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

            if ($stmt->rowCount() > 0) {
                return $this->find($value, ['id']);
            }

            return false;
        } catch (PDOException $e) {
            throw new PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        } catch (\Exception $e) {
            throw new Exception("Error interno: " . $e->getMessage(), 500);
        }
    }

    /**
     * Elimina registros de la tabla especificada.
     * 
     * @param mixed $value Valor de la condición WHERE.
     * @param string $operator Operador de comparación (por ejemplo, '=', '>', '<', etc.).
     * @param string $column Columna para la condición WHERE (por defecto 'id').
     * @return true Si el registro fue eliminado con exito.    
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.
     * @throws Exception Si ocurre un error interno.
     * 
     */
    public function delete($value, $operator = '=', $column = 'id')
    {
        $this->getTable();
        $user = $this->find($value, ['id'], $operator, $column);

        if (!$user) {
            throw new \PDOException("Error in Jenyus\Base\DynamicModel: el registro no existe", 404);
            return;
        }

        try {
            $sql = "DELETE FROM {$this->table} WHERE {$column} {$operator} :value";
            $stmt = $this->conexion->prepare($sql);
            $param_type = is_int($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $stmt->bindParam(':value', $value, $param_type);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return true; // Indicar que se eliminó exitosamente
            }
            throw new \RuntimeException('No se eliminaron filas');
        } catch (\PDOException $e) {
            throw new \PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        } catch (\Exception $e) {
            throw new \Exception("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        }
    }
}
