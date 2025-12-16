# Tareas

A trabajar papu.

## Trabajando

- [ ] Crear un sistema de eventos.
  - [x] Instalar `psr/event-dispatcher`.
  - [x] Crear una implementación para el dispatcher.
  - [ ] Incluir el dispatcher al container.
  - [ ] Crear un archivo de configuración para listar los eventos y sus listener.

## Módulos

Lista de módulos que faltan por hacer.

### Tasks

Módulos que son necesarios para poder comenzar a ejecutar playbooks.

Formato:

- [ ] `keyword`: explicación.

#### Prioridad alta

- [ ] `ask`: para hacer preguntas al usuario y guardar el valor y ser reutilizado por otras tareas.
- [ ] `php`: para ejecutar archivos php.
- [ ] `copy`: para copiar archivos.

#### Prioridad media

- [ ] `composer`: para todas las operaciones con composer.
  - [ ] `create`: para crear proyectos.
  - [ ] `package`: para los paquetes de composer. Podría hacer como ansible y usar el parámetro `state` para determinar el comportamiento.
- [ ] `shell`: para ejecutar comandos en la terminal.
- [ ] `log`: para registrar logs (cambiar el ya existente).

#### Prioridad baja

- [ ] `replace`: para reemplazar textos en archivos.
- [ ] `lineinfile`: para agregar o modificar una línea de un archivo (enfocado en archivos de configuración).
- [ ] `template`: para generar archivos a partir de plantillas.
- [ ] `git`: para operaciones con git.
- [ ] `json`: para manipular archivos json.

### Directivas

Directivas del playbook por implementar.

#### Prioridad media

- [ ] `require`: para verificar que ciertas aplicaciones estén instaladas antes de ejecutar las tareas.
- [ ] `vars`: para definir valores antes de la ejecución de las tareas.

#### Prioridad baja

- [ ] `include`: para cargar otros playbooks.
- [ ] `version`: para indicar con qué versión de la aplicación es compatible el playbook.
- [ ] `max_fail_percentage`: para indicar el porcentaje de fallas permitido antes de detenerse.

## Sistemas

- [ ] Evaluación de valores. Un sistema que se encargue de evaluar tal y como lo hace Jinja, pero más simple...
