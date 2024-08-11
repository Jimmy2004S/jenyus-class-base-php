<?php

namespace Jenyus\Base;

use Exception;
use InvalidArgumentException;
use Jenyus\Base\Util\Auth;
use Jenyus\Base\Util\ModelFormat;
use PDO;
use PDOException;

class DynamicModel extends Methods
{

    use Auth;
    /**
     * Constructor de la clase DynamicModel.
     * 
     * @param PDO $conexion Objeto PDO para la conexión a la base de datos.
     * @param $table Definir la tabla con la que trabajaras.
     * @throws PDOException Si no se proporciona una instancia válida de PDO.
     */
    public function __construct(PDO $conexion, $table)
    {
        if (!$conexion instanceof PDO) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: Se requiere una instancia válida de PDO.", 422);
        }
        if (!$table || empty($table) || $table == '') {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: La propiedad 'table' no puede ser null, debe proporcionar un valor en dentro del constructor de la clase.", 422);
        }
        parent::__construct($conexion, $table);
    }

    public function setTable($table){
        if (!is_string($table)) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: 'table' property must be a string. Please provide a valid table name.", 422);
        }
        parent::SetTable($table);
    }
    
}
