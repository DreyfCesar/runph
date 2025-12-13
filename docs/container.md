# Contenedor de servicios

El contenedor de servicios es el encargado de crear las instancias de las clases y resolver sus dependencias automáticamente.

## Características

### Alias

Se pueden definir alias para que al crear la instancia se use la indicada.

Ejemplo:

```php
<?php

$container->set(Interfaz::class, Implementacion::class);
$result = $container->get(Interfaz::class);
```

En este caso `Interfaz::class` es el alias para `Implementacion::class`. Cuando se quiera crear una instancia de `Interfaz::class` se obtendrá una instancia de la clase `Implementacion`.
