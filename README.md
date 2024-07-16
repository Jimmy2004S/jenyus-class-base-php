# Paquete PHP: jenyus/class-base-php

Este paquete proporciona una clase base para realizar consultas dinámicas en bases de datos utilizando PHP y PDO.

## Instalación

Puedes instalar este paquete a través de Composer. Ejecuta el siguiente comando en tu terminal:
```
composer require jenyus/class-base-php
```

## Uso básico

Para comenzar a utilizar la clase `DynamicModel` proporcionada por este paquete, sigue estos pasos:

### 1. Configuración de la conexión a la base de datos

Asegúrate de tener una instancia de PDO configurada correctamente para tu base de datos. Aquí tienes un ejemplo básico de cómo configurar la conexión usando el patron de diseño singleton:

```php
<?php 
namespace DataBase\Config;

class DB{
    private $schema;
    private $host;
    private $password;
    private $user;
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $this->schema = 'nombre_de_la_bd';
        $this->host = 'localhost';
        $this->password = 'password';
        $this->user = 'user';

        try {
            $this->pdo = new \PDO("mysql:host=$this->host;dbname=$this->schema", $this->user, $this->password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception("No se pudo conectar a la base de datos: " . $e->getMessage());
        }
    }

    public static function getConnection()
    {
        if (!self::$instance) {
            self::$instance = new DB();
        }
        return self::$instance->pdo;
    }
}
```

### 2. Extender de la clase del paquete
```php
namespace App\Model;

use DataBase\Config\DB;
use Jenyus\Base\DynamicModel;

class User extends DynamicModel
{
    protected $table = 'users'; // Nombre de la tabla en la base de datos

    public function __construct()
    {
        $this->connection = DB::getConnection();
        parent::__construct($this->connection);  // Llama al constructor del padre (DynamicModel) pasando la conexion a tu basa de datos
    }
}
```


### Realizar consultas:

Utiliza los métodos proporcionados por DynamicModel para ejecutar consultas en la base de datos. Aquí tienes algunos ejemplos:

Crear una instancia del modelo
  $userModel = new User();

Ejemplo de consulta: obtener todos los usuarios
$users = $userModel->all()->get();
 
Ejemplo de consulta: obtener un usuario por ID
$user = $userModel->find(1)->first();



### Métodos disponibles en DynamicModel

#### - table($table)
Establece la tabla con la que trabajar.
#### - query($sql)
Ejecuta una consulta SQL personalizada.
####- all($columns = ['*'])
Ejecuta una consulta para obtener todos los registros.
#### - where($column, $value, $operator, $columns = ['*'])
Ejecuta una consulta con una cláusula WHERE.
#### - find($value, $columns = ['*'], $operator = '=', $column = 'id')
Ejecuta una consulta para encontrar un registro por ID u otra columna.
#### - insert($columns = [], $values = [])
Ejecuta una consulta de inserción en la base de datos.
#### - update($columns = [], $value, $operator = '=', $column = 'id')
Ejecuta una consulta de actualización en la base de datos.
#### - delete($value, $operator = '=', $column = 'id'): Ejecuta una consulta de eliminación en la base de datos.

