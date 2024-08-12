<?php

namespace Jenyus\Base;

use InvalidArgumentException;
use PDO;
use PDOException;

class DynamicModel extends Methods
{

    protected $table;
    /**
     * Constructor de la clase DynamicModel.
     * 
     * @param PDO $conexion Objeto PDO para la conexión a la base de datos.
     * @param $table Definir la tabla con la que trabajaras.
     * @throws PDOException Si no se proporciona una instancia válida de PDO.
     */
    public function __construct(PDO $conexion)
    {
        if (!$conexion instanceof PDO) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: Se requiere una instancia válida de PDO.", 422);
        }

        parent::__construct($conexion);
        parent::setTable($this->table);
    }

    public function setTable($table){
        if (!is_string($table)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: 'table' property must be a string. Please provide a valid table name.", 422);
        }

        if($this->table === null || $this->table === '') {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: 'table' no puede ser null. Por favor proporcione un valor.", 422);
        }

        parent::SetTable($table);
    }
    
}
