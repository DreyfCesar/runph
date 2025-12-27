# Interpolator

El interpolador es básicamente un coso que reemplaza variables en strings. Le das un texto con placeholders tipo `${nombre}` o `$nombre` y los reemplaza con valores guardados en una memoria.

## SimpleInterpolator

Esta es la implementación básica. Simple pero cumple.

### Cómo funciona

```php
$memory = new Memory();
$memory->set('nombre', 'Juan');
$memory->set('edad', 25);

$interpolator = new SimpleInterpolator($memory);

$resultado = $interpolator->interpolate('hola ${nombre}, tenés ${edad} años');
// $resultado: "hola juan, tenés 25 años"
```

### Sintaxis soportada

Hay dos formas de escribir las variables:

**Con llaves:**
```php
$interpolator->interpolate('${variable}');
```

**Sin llaves:**
```php
$interpolator->interpolate('$variable');
```

Las dos funcionan igual, pero las llaves son más claras cuando hay texto pegado: `"archivo_${nombre}.txt"` vs `"archivo_$nombre.txt"`.

### Tipos de valores

El interpolador acepta:
- **Strings**: `"hola"`
- **Números**: `42`, `3.14`, `0`

Si se intenta interpolar algo que no sea string o un número va a lanzar una excepción.

### Variables que no existen

Si una variable no está en memoria, el interpolador*no hace nada y deja el placeholder como está:

```php
// Si 'inexistente' no está en memoria:
$interpolator->interpolate('Valor: ${inexistente}');
// Resultado: "Valor: ${inexistente}"
```

Por ahora me sirve para debuggear qué variables faltan, pero puede que cambie para que lance algún error o algo si no existe en el futuro cuando lo necesite.

### Escapar variables

```php
$interpolator->interpolate('El precio es \$${precio}');
// Resultado: "El precio es $100" (si precio = 100)

$interpolator->interpolate('Usá \${variable} para interpolar');
// Resultado: "Usá ${variable} para interpolar"
```

### Ejemplo completo

```php
$memory = new Memory();
$memory->set('usuario', 'María');
$memory->set('precio', 1500);

$interpolator = new SimpleInterpolator($memory);

$mensaje = $interpolator->interpolate(
    'Hola ${usuario}, este playbook cuesta \$${precio}'
);

echo $mensaje;
// Output: "Hola María, este playbook cuesta $1500"
```

## Reglas a tener en cuenta

- Los nombres de variables solo pueden contener letras, números y guiones bajos (`a-z`, `A-Z`, `0-9`, `_`)
- Las variables deben estar en memoria antes de interpolar
- Para escapar hay que usar `\$`
