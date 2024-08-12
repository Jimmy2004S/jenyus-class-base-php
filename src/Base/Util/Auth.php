<?php

namespace Jenyus\Base\Util;

use InvalidArgumentException;
use PDOException;

trait Auth
{

    protected $model_id;

    /**
     * Busca un registro en la tabla con la condición especificada.
     *
     * @param array $columns Array asociativo con columnas a evaluar ( example: username or email, password)
     * @return $this True si la autenticacion es correcta
     * @return null Si el usuario no existe
     * @return false Si la contraseña es incorrecta
     * @throws InvalidArgumentException Si los argumentos no son válidos.
     */
    public function login($columns = [])
    {
        if (count($columns) > 2) {
            throw new \InvalidArgumentException("No puedes usar mas de dos campos para este metodo de autenticacion", 400);
        }

        if (!array_key_exists('password', $columns) && !array_key_exists('contraseña', $columns)) {
            throw new \InvalidArgumentException("Debe existir un campo de contraseña o password para este metodo de autenticacion", 400);
        }

        if (!is_array($columns) || array_values($columns) === $columns) {
            throw new InvalidArgumentException("Error in Jenyus\Base\DynamicModel: The argument must be an associative array", 422);
        }

        $key = array_key_first($columns);

        // Busca el usuario en la base de datos
        $user = $this->where($key, $columns[$key], '=', ['password', 'id'])->first();

        if (!$user) {
            return null;
        }

        //Apunta el indice a la siguiente clave
        next($columns);

        // Obtiene la segunda clave
        $key2 = key($columns);

        if (password_verify($columns[$key2], $user['password'])) {
            $this->model_id = $user['id'];
            return $this;
        }

        return false;
    }


    /**
     * Crea un token para el usuario logueado
     * 
     * Arquitectura mvc por lo cual
     * $columns['tokenable_type'] = 'App\Model\\' . $model;
     * nota: este campo no representa conflictos, podrias bien no usarlo.
     *
     * @param array $columns Array asociativo con columnas a insertar
     * @param array $abilities Array con las habilidades que tendra el token
     * @param array $table String con el nombre de la tabla para los token ( por defecto: 'personal_access_tokens' )
     * @return string token generado para la session del usuario : 'model_id|hash'
     * @return false Si no se genero ningun token
     * @throws InvalidArgumentException Si no hay un id valido o intento de sesion.
     */
    public function generateToken($abilities = [], $name = 'auth_token', $columns = ['tokenable_type' => '', 'tokenable_id' => '', 'name' => '', 'token' => '', 'abilities' => ['']], $table = 'personal_access_tokens')
    {
        if (!$this->model_id) {
            throw new InvalidArgumentException("User is not authenticated, unable to generate token.");
        }

        $token = $this->model_id . '|' . bin2hex(random_bytes(32));
        $columns['token'] = $token;
        $columns['tokenable_id'] = $this->model_id;
        $model = substr($this->table, 0, -1);
        $model = ucfirst($model);
        $columns['tokenable_type'] = 'App\Model\\' . $model;
        $columns['abilities'] = json_encode($abilities);
        $columns['name'] = $name;

        try {

            $sql = $this->insertSQL($columns, $table);

            $values = [];

            foreach ($columns as $key => $value) {
                $values[':' . $key] = $value;
            }

            $this->prepare($sql);

            $this->bindValue($values, $this->query);

            $this->query->execute();

            return ($this->query->rowCount() > 0)  ? $token : false;
        } catch (\PDOException $e) {
            throw new \PDOException("Error generating token in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        }
    }


    /**
     * Borrar todos los tokens ( todas las sesiones del usuario )
     *
     * @param string $token token generado en la session
     * @param string $table tabla que manejas para los tokens ( default = 'personal_access_tokens')
     * @param array $table String con el nombre de la tabla para los token ( por defecto: 'personal_access_tokens' )
     * @return boolean 
     * @throws PDOException
     */
    public function revokarTokens($token, $table = 'personal_access_tokens')
    {
        $sql = $this->deleteSQL('=', 'tokenable_id', $table);
        $token = explode('|', $token);
        try {
            $this->prepare($sql);

            $this->bindParam($token[0], $this->query);

            $this->query->execute();

            return ($this->query->rowCount() > 0) ? true : false;
        } catch (PDOException $e) {
            throw new PDOException("Error revoking tokens in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        }
    }

    /**
     * Borrar un token ( session actual )
     * 
     * @param string $token token generado en la session
     * @param string $table tabla que manejas para los tokens ( default = 'personal_access_tokens')
     * @param array $table String con el nombre de la tabla para los token ( por defecto: 'personal_access_tokens' )
     * @return boolean 
     * @throws PDOException 
     */
    public function revokarToken($token, $table = 'personal_access_tokens')
    {

        $sql = $this->deleteSQL('=', 'token', $table);

        try {
            $this->prepare($sql);

            $this->bindParam($token, $this->query);

            $this->query->execute();

            return ($this->query->rowCount() > 0) ? true : false;
        } catch (PDOException $e) {
            throw new PDOException("Error revoking tokens in Jenyus\Base\DynamicModel: " . $e->getMessage(), 500);
        }
    }
}
