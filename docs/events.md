# Eventos

El sistema de eventos es simple, pero funcional.

## Crear un listener

Para crear un listener no se necesita implementar ninguna interface, pero sí se requiere de la declaración del método `handle()` con un parámetro del tipo del evento en cuestión.
El sistema no hace ninguna evaluación de tipos (por ahora), por lo que se espera que el método tenga siempre un solo parámetro de tipo `object` donde se pasará la instancia del evento.

## Registrar un listener

Para registrar un listener hay que modificar el archivo de configuración `listeners` y agregar el `class-string` del evento como clave y el `class-string` del listener como valor.
También se puede definir un array de `class-string` como valor. De este modo se puede registrar varios listeners a un evento.

```php
<?php

return [
    // Un solo listener
    CommandFailed::class => LogErrorListener::class,
    
    // Múltiples listeners
    PlaybookCompleted::class => [
        GenerateExecutionReportListener::class,
        CleanupTemporaryFilesListener::class,
        SendNotificationListener::class,
    ],
];
```
