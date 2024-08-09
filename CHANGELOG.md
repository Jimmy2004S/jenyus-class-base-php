
Todos los cambios notables en este proyecto se documentarán en este archivo.

# Como actualizar el paquete
 ``` composer update ``` 

## [No lanzado]
### Añadido

### Corregido


## [1.1.1] - 2024-07-27
### Añadido
- Metodo de login para contraseñas encriptadas (bcrypt)


## [1.1.0] - 2024-07-27
### Añadido
- Pasar el argumento de la tabla  por medio del mismo constructor

### Cambiado
- Metodo retornan valores mas coherentes.
- Se agregan las anotaciones para las excepciones que deben ser manejadas en cada metodo


### Corregido
- El metodo eliminar devuelve un valor true en caso de eliminacion y no un id.
