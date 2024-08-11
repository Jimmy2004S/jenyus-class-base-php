<?php

namespace Jenyus\Base\Util;

use InvalidArgumentException;

trait Auth
{

    /**
     * Busca un registro en la tabla con la condición especificada.
     *
     * @param array $columns Array asociativo con columnas a evaluar ( example: username or email, password)
     * @return true True si la autenticacion es correcta
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
        $user = $this->where($key, $columns[$key], '=', ['password'])->first();

        if (!$user) {
            return null;
        }

        //Apunta el indice a la siguiente clave
        next($columns);

        // Obtiene la segunda clave
        $key2 = key($columns);

        if (password_verify($columns[$key2], $user['password'])) {
            return true;
        }

        return false;
    }
}
