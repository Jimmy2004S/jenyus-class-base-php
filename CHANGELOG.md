
Todos los cambios notables en este proyecto se documentarán en este archivo.

# Como actualizar el paquete
 ``` composer update ``` 

## [No lanzado]
### Añadido
- 

### Corregido

## [1.1.4] - 2024-12-08

### Corregido
- Metodo eliminar

## [1.1.4] - 2024-12-08

### Corregido
- Se devuelve un string con una copia del token mas el id para facilitar su uso
- Correcion del metodo eliminar token individual

## [1.1.3] - 2024-12-08
### Añadido
- Metodo para eliminar tokens

## [1.1.2] - 2024-12-08
### Añadido
- Metodo para generar token de autenticacion

#### Cambiado
- Se elimina el atributo protected $table de la clase
- El constructor de la clase exige como segundo parametro el atributo $table

### Corregido
- Metodo login devuelve su propia instancia para aceptar el metodo generateToken ( opcional)

## [1.1.1] - 2024-07-27
### Añadido
- Metodo de login para contraseñas encriptadas (bcrypt)


### [1.1.0] - 2024-07-27
#### Añadido
- Pasar el argumento de la tabla  por medio del mismo constructor

#### Cambiado
- Metodos retornan valores mas coherentes.
- Se agregan las anotaciones para las excepciones que deben ser manejadas en cada metodo


### Corregido
- El metodo eliminar devuelve un valor true en caso de eliminacion y no un id.
