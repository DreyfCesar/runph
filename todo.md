# Tareas

A trabajar papu.

## Trabajando

- [ ] Refactorizar la ejecución de tareas.
  - [ ] Reemplazar `assert()` por una excepción en MetaHandler.
  - [x] Hacer pruebas unitarias de MetaHandler.
    - [x] Probar que funciona como se espera.
    - [x] Probar que se ejecutan los handlers en el orden esperado.
    - [x] Probar los múltiples casos de `shouldSkip()`.
  - [ ] Hacer pruebas unitarias de Register.
  - [ ] Hacer pruebas unitarias de RegisterFactory (¿innecesario?).
  - [x] Corregir las pruebas fallidas de TasksDirectiveTest por los cambios realizados.

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

### Eventos

- [ ] Agregar modo debug.
  - [ ] Mostrar eventos que se disparan.
  - [ ] Mostrar listeners ejecutados.
  - [ ] Detectar propagación detenida.
