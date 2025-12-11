# Runph

Para acordarme de cosas si me llego a olvidar...

## Playbook

### Módulos

Los módulos son las partes de un playbook que se ejecutan siguiendo las siguientes reglas:

- La clase debe implementar `Runph\Playbook\Contracts\ModuleInterface`.
- El constructor debe definir las dependencias que necesite.
- Las propiedades de tipo `scalar` del constructor serán resueltas por el contenedor de servicios con los datos proporcionados por el playbook.
- Por defecto la propiedad `$value` siempre tendrá el valor completo.

Cuando en el playbook se usa un módulo se hace de la siguiente forma.

```yaml
module_name: value
```

Cada módulo puede tener un valor de cualquier tipo, pero si es un array este será usado para resolver las dependencias `scalar` de la clase que la gestiona.

Por ejemplo, para un playbook así:

```yaml
module_name:
    - foo: foo
      bar: bar
```

Se resolverían las dependencias de una clase como esta:

```php
<?php
class ModuleName implements \Runph\Playbook\Contracts\ModuleInterface
{
    public function __construct($foo, $bar, Baz $baz) {}
}
```

También se puede especificar el tipo esperado.

```yaml
module_name:
    - text: foo bar
      numeric: 12345
      floating: 1.234
      boolean: true
```

El sistema se encargaría de verificar que coincidan los tipos esperados siempre y cuando se especifique en la propiedad.

```php
<?php
class ModuleName implements \Runph\Playbook\Contracts\ModuleInterface
{
    public function __construct(string $text, int $numeric, float $floating, bool $boolean, Foo $foo) {}
}
```

En caso de que no haya coincidencia de tipos se lanza una excepción `InvalidParameterTypeException`.

Y además se pueden obtener todos los valores en un solo argumento.

```php
<?php
class ModuleName implements \Runph\Playbook\Contracts\ModuleInterface
{
    public function __construct(string $text, int $numeric, float $floating, bool $boolean, array $value, Foo $foo)
    {
        /*
            $value = [
                'text' => 'foo bar',
                'numeric' => 12345,
                'floating' => 1.234,
                'boolean' => true,
            ];
        */
    }
}
```

De este modo se pueden obtener valores que no son de tipo array.

```yaml
log: foo bar baz
```

```php
<?php
class LogTask implements \Runph\Playbook\Contracts\ModuleInterface
{
    public function __construct(string $value, OutputInterface $output)
    {
        $output->writeln($value);
    }
}
```

> **Nota:** El orden de los parámetros no altera el comportamiento.

### Directivas

Las directivas son los módulos principales de un playbook. Por ahora solo hay dos: `name` y `tasks`. El primero indica el nombre del playbook mientras que el segundo almacena todas las tareas que se ejecutarán.

```yaml
name: Nombre del playbook

tasks:
    - name: Nombre de la tarea
      module: Módulo a ejecutar
```

#### Crear una directiva

Para crear una directiva se necesita modificar el archivo de configuración `config/directives.php`, donde están las directivas asociadas a una keyword, y crear la clase encargada de gestionarlo.

```php
<?php

return [
    'keyword_name' => DirectiveClassName::class,
];
```

Las directivas están en la carpeta `src/Playbook/Modules/Directives/`. Toda directiva es un [módulo](#módulos), por lo que su comportamiento es similar.
