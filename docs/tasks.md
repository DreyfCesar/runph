# Tasks

Documentación de cada tarea y sus propiedades.

## AskTask

**Alias**: `ask`.

### Descripción

Esta tarea sirve para hacer preguntas al usuario y almacenar la respuesta en una memoria compartida entre tareas.

### Propiedades

- **message**: mensaje con la pregunta
  - tipo: `string`
  - obligatorio
- **save**: key con el que se asociará la respuesta en la memoria
  - tipo: `string`
  - obligatorio
- **default**: respuesta por defecto
  - tipo: `string`
  - opcional
  - valor por defecto: ''
- **hidden**
  - tipo: `bool`
  - opcional
  - valor por defecto: `false`

Ejemplo de uso:

```yaml
- ask:
  message: ¿Cuál es tu contraseña de paypal?
  default: 123cuatrocincoseis789
  save: paypal_password
  hidden: true
```
