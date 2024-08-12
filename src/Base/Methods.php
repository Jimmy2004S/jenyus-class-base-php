<?php

namespace Jenyus\Base;

use InvalidArgumentException;
use Jenyus\Base\Util\Auth;
use Jenyus\Base\Util\ModelFormat;
use Jenyus\Base\Util\sqlConstruct;
use PDO;
use PDOException;
use RuntimeException;

class Methods
{

    use ModelFormat, sqlConstruct, Auth;

    protected $conexion;
    protected $table;
    protected $query;

    public function __construct(PDO $conexion)
    {
        $this->conexion = $conexion;
    }

    /**
     * Establece la tabla en la que se ejecutarán las consultas.
     * 
     * @param string $table Nombre de la tabla en la base de datos.
     */
    protected function SetTable($table)
    {
        $this->table = $table;
    }

    /**
     * Ejecuta una consulta SQL personalizada.
     * 
     * @param string $sql Consulta SQL a ejecutar.
     * @return $this->query
     * @throws PDOException Si ocurre un error con la base de datos.
     */
    public function prepare($sql)
    {
        try {
            $this->query = $this->conexion->prepare($sql);
        } catch (PDOException $e) {
            throw new PDOException('Error in Jenyus\Base\DynamicModel: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Ejecuta una consulta SQL personalizada.
     * 
     * @param string $sql Consulta SQL a ejecutar.
     * @return $this->query 
     * @throws PDOException Si ocurre un error con la base de datos.
     */
    public function query($sql)
    {
        try {
            $this->query = $this->conexion->query($sql);
        } catch (PDOException $e) {
            throw new PDOException('Error in Jenyus\Base\DynamicModel: ' . $e->getMessage(), 500);
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
        if (!$this->query) {
            throw new \PDOException("No se ha ejecutado ninguna consulta previamente.");
        }

        if($this->query->rowCount() < 0){
            return false;
        }

        try {
            
            $result = $this->query->fetchAll(PDO::FETCH_ASSOC);

            return $result ?? false;

        } catch (PDOException $e) {
            throw new PDOException('Error in Jenyus\Base\DynamicModel: ' . $e->getMessage());
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
        if (!$this->query) {
            throw new \PDOException("Error in Jenyus\Base\DynamicModel: No se ha ejecutado ninguna consulta previamente.");
        }

        if($this->query->rowCount() < 0){
            return false;
        }
        
        try {

            $result = $this->query->fetch(PDO::FETCH_ASSOC);
            return $result ?? false;
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

        try {

            $sql = $this->selectSQL($columns, $this->table);

            $this->query($sql);

            $this->query->execute();

            return ($this->query->rowCount() > 0) ? $this->get() : false;
        } catch (PDOException $e) {
            throw new PDOexception('Error in Jenyus\Base\DynamicModel: ' . $e->getMessage());
        }
    }

    /**
     * Ejecuta una consulta con una cláusula WHERE.
     * 
     * @param string $column Columna para la condición WHERE.
     * @param mixed $value Valor a comparar.
     * @param string $operator Operador de comparación (por ejemplo, '=', '>', '<', etc.).
     * @param array $columns Columnas a seleccionar (por defecto ['*']).
     * @return $this
     * @return array Arreglo con el resultado de la consulta.
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.
     * @throws Exception Si ocurre un error interno.
     */
    public function where($column, $value, $operator = '=', $columns = ['*'])
    {
        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be array", 422);
        }

        try {

            $sql = $this->whereSQL($value, $columns, $operator, $column, $this->table);

            $this->prepare($sql);

            $this->bindParam($value, $this->query);

            $this->query->execute();

            return $this;
            
        } catch (PDOException $e) {
            throw new PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        }
    }

    /**
     * Busca un registro por su valor en una columna específica.
     * 
     * @param mixed $value Valor a buscar.
     * @param array $columns Columnas a seleccionar (por defecto ['*']).
     * @param string $operator Operador de comparación (por ejemplo, '=', '>', '<', etc.).
     * @param string $column Columna para la condición de búsqueda (por defecto 'id').
     * @return array Arreglo asociativo con el resultado de la consulta.
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     * @throws PDOException Si ocurre un error con la base de datos.
     */
    public function find($value, $columns = ['*'], $operator = '=', $column = 'id')
    {
        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: the argumemnt must be array", 422);
        }

        try {

            $sql = $this->whereSQL($value, $columns, $operator, $column, $this->table);

            $this->prepare($sql);

            $this->bindParam($value, $this->query);

            $this->query->execute();

            return ($this->query->rowCount() > 0) ? $this->first() : false;

        } catch (PDOException $e) {
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
    public function insert($columns = [], $date = true)
    {
        if (!is_array($columns)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be associative array", 422);
        }

        // Añadir 'created_at' a las columnas y su valor correspondiente a los valores
        if ($date) {
            $columns['created_at'] = $this->basicCurrentFormatDate();
        }

        try {
            $sql = $this->insertSQL($columns, $this->table);

            $values = [];
            foreach ($columns as $key => $value) {
                $values[':' . $key] = $value;
            }

            $this->prepare($sql);

            $this->bindValue($values, $this->query);

            $this->query->execute();

            return ($this->query->rowCount() > 0)  ? $this->conexion->lastInsertId() : false;
        } catch (PDOException $e) {
            throw new PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
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
    public function update($columns, $value, $operator = '=', $column = 'id')
    {

        $user = $this->find($value, ['id'], $operator, $column);

        if (!$user) {
            throw new RuntimeException("Error in Jenyus\Base\DynamicModel: el recurso no existe", 404);
        }

        $sql = $this->updateSQL($columns, $value, $operator, $column, $this->table);

        try {

            $this->prepare($sql);
            $this->bindValue($columns, $this->query);
            // Enlazar el valor para la cláusula WHERE
            $this->bindParam($value, $this->query);

            $this->query->execute();

            return ($this->query->rowCount() > 0) ? $user['id'] : false;
        } catch (PDOException $e) {
            throw new PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
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
        $user = $this->find($value, ['id'], $operator, $column);

        if (!$user) {
            throw new \PDOException("Error in Jenyus\Base\DynamicModel: el registro no existe", 404);
            return;
        }

        $sql = $this->deleteSQL($value, $operator, $column, $this->table);

        try {

            $this->prepare($sql);

            $this->bindParam($value, $this->query);

            $this->query->execute();

            return ($this->query->rowCount() > 0) ? true : false;
        } catch (\PDOException $e) {
            throw new \PDOException("Error in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        }
    }
}
