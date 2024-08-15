
Todos los cambios notables en este proyecto se documentarán en este archivo.

# Como actualizar el paquete
 ``` composer update ``` 

## [No lanzado]
### Añadido
- 

### Corregido

## [1.1.8] - 2024-15-08

### Corregido
- Permitir cambiar el valor del atributo $table usando la misma instancia, evitando conflicto entre consultas

#### Cambiado
- Se permite definir el atributo $table atraves del constructor (opcional)


## [1.1.7] - 2024-15-08

### Corregido
- Metodo find


## [1.1.6] - 2024-14-08

### Añadido
- Realizar consultas con where anidados (AND, OR)

## [1.1.5] - 2024-13-08

### Corregido
- Metodo delete
- Metodo update

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
