# Paquete PHP: jenyus/class-base-php

Este paquete proporciona una clase base para realizar consultas dinámicas en bases de datos utilizando PHP y PDO.

## Instalación

Puedes instalar este paquete a través de Composer. 
1. Instalar composer en su proyecto
``` composer install ```

2. Permitir la compatibilidad con el paquete agregando la siguiente linea en el archivo composer.json 
   
      ``` "minimum-stability": "dev" ```

3. Ejecuta el siguiente comando en tu terminal:
   
    ```composer require jenyus/class-base-php```

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

### 2. Extender de la clase del paquete (DynamicModel)
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
         // Llama al constructor del padre (DynamicModel) pasando la conexion a tu base de datos
        parent::__construct($this->connection);  
    }
}
```


### Realizar consultas:

Utiliza los métodos proporcionados por DynamicModel para ejecutar consultas en la base de datos. Aquí tienes algunos ejemplos:

Crear una instancia del modelo
  $userModel = new User();

* obtener todos los usuarios
$users = $userModel->all();
 
* obtener un usuario por ID
$user = $userModel->find(1)->first();



## Métodos disponibles en DynamicModel y ejemplos de como usarlos

#### - SetTable($table)
Establece la tabla con la que trabajar.
#### - query($sql)
Ejecuta una consulta SQL personalizada.
#### - all($columns = ['*'])
Ejecuta una consulta para obtener todos los registros y recibe como parametro opcional un arreglo con las columnas deseadas.

```$users = $userModel->all(
   ['id', 'nombre', 'email']
);```

#### - where($column, $value, $operator, $columns = ['*'])
Ejecuta una consulta con una cláusula WHERE. 

```$users = $userModel->where('estado', 'activo')->get();```

```$users = $userModel->where('estado', 'activo', '!=',
   ['user_name', 'role']
)->get();```

#### - find($value, $columns = ['*'], $operator = '=', $column = 'id')
Ejecuta una consulta para encontrar un registro por ID u otra columna.

```$user = $userModel->find(2)->first(); //Este es el uso mas simple, si se desea realizar otros metodos de busqueda se recomienda usar el metodo where ( aunque este metodo tambien permite algunos) ``` 

#### - insert($columns = [])
Ejecuta una consulta de inserción en la base de datos. Recibe como parametro un array asociativo con las columnas y valores a insertar.

 ``` $id = $userModel->insert([
                'nombre' => 'Manuel', 
                'email' => 'Suarez', 
                'role' => 1
            ]);
```

#### - update($columns = [], $value, $operator = '=', $column = 'id')
Ejecuta una consulta de actualización en la base de datos.

 ``` $id = $userModel->update([
                'nombre' => 'Manuel', 
                'email' => 'Anaya', 
                'role' => 2
            ], 44);
```


#### - delete($value, $operator = '=', $column = 'id')
Ejecuta una consulta de eliminación en la base de datos.

 ``` $bolean = $userModel->delete(44);```


## Manejo de Excepciones

Los métodos de `DynamicModel` pueden lanzar varias excepciones que es importante manejar para garantizar que tu aplicación pueda responder adecuadamente a diferentes tipos de errores. Aquí hay una lista de las excepciones comunes que puedes esperar y cómo manejarlas:

### Excepciones comunes

- `InvalidArgumentException`: Se lanza cuando los argumentos proporcionados a un método no son válidos.
- `PDOException`: Se lanza cuando ocurre un error relacionado con la base de datos.
- `Exception`: Se lanza cuando ocurre un error interno no específico.

### Ejemplo de manejo de excepciones

```php
try {
    $userModel = new User();
    $users = $userModel->all();
} catch (InvalidArgumentException $e) {
    echo "Argumento inválido: " . $e->getMessage();
} catch (PDOException $e) {
    echo "Error en la base de datos: " . $e->getMessage();
} catch (Exception $e) {
    echo "Error interno: " . $e->getMessage();
}


# Notas
#### Columna created_at
   * Las tablas en tu base de datos necesitan tener una columna llamada created_at. La clase 'DynamicModel' maneja automaticamente, la fecha actual en la que se crea el registro.
   * Si no estas usando esta columna puedes pasar en el metodo insert un 'false' como segundo parametro.
     ```class->insert([] , false)```
#### Metodos mas faciles de usar los metodos
   * Luego de haber extendido tu clase modelo de mi la clase 'DynamicModel' puedes usar las mismas instancias de esa unica clase para hacer distintas consultas

 ```php
namespace App\Model;

use DataBase\Config\DB;
use Jenyus\Base\DynamicModel;

class Model extends DynamicModel
{
    public function __construct($table)
    {
        $this->connection = DB::getConnection();
         // Llama al constructor del padre (DynamicModel) pasando la conexion a tu base de datos
        parent::__construct($this->connection, $table);  
    }
}


$model = new Model('users')
$users = $model->all();

// Y luego podrias cambiar facilmente la tabla
$model->SetTable('students');
$students = $model->all();

```


## Actualizaciones
https://github.com/Jimmy2004S/jenyus-class-base-php/blob/main/CHANGELOG.md
