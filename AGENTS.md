# AGENTS.md

## 1. Propósito del proyecto

Este repositorio contiene una plataforma B2B de educación financiera desarrollada para KATO-KI.

La plataforma está orientada principalmente a estudiantes y permitirá que instituciones educativas asignen contenido de educación financiera organizado mediante la siguiente estructura:

`Curso → Nivel → Módulo → Contenido → Actividades → Progreso → Diploma`

El sistema debe priorizar:

* seguridad;
* mantenibilidad;
* simplicidad;
* experiencia de usuario;
* diseño responsive;
* experiencia móvil;
* accesibilidad;
* progreso secuencial;
* integridad histórica;
* consistencia visual;
* rendimiento;
* claridad del código.

---

# 2. Idioma oficial del proyecto

El idioma oficial del proyecto es **español**.

Todo el código perteneciente al dominio de la aplicación deberá utilizar nombres en español.

Esto incluye:

* modelos;
* servicios;
* policies propias;
* componentes Livewire;
* recursos Filament;
* relaciones;
* métodos de dominio;
* propiedades propias;
* variables;
* enums propios;
* factories;
* seeders;
* pruebas;
* rutas nombradas;
* tablas;
* columnas;
* índices;
* claves foráneas;
* claves JSON;
* textos de interfaz.

No utilizar nombres en inglés para entidades del dominio cuando exista una equivalencia clara en español.

Ejemplos correctos:

```text
Colegio
GradoAcademico
Usuario
Curso
Nivel
Modulo
Capsula
AsignacionCurso
IntentoActividad
ProgresoModuloUsuario
Diploma
```

Ejemplos que deben evitarse:

```text
School
AcademicDegree
User
Course
Level
Module
Capsule
CourseAssignment
ActivityAttempt
UserModuleProgress
```

## Excepciones

No traducir nombres pertenecientes directamente al framework, librerías o estándares.

Por ejemplo:

```text
Model
Resource
RelationManager
Policy
Middleware
Builder
Livewire
Filament
Tailwind
Alpine
Laravel
PostgreSQL
JSONB
```

Por tanto, son nombres válidos:

```text
ColegioResource
CursoResource
ModuloResource
CapsulaRelationManager
CursoPolicy
ServicioProgreso
```

---

# 3. Convenciones de nombres

Todos los identificadores propios deberán escribirse:

* en español;
* sin tildes;
* sin `ñ`;
* sin caracteres especiales.

## Clases PHP

Usar `StudlyCase`.

Ejemplos:

```text
GradoAcademico
AsignacionCurso
IntentoActividad
ProgresoModuloUsuario
ServicioProgreso
```

## Métodos y variables PHP

Usar `camelCase`.

Ejemplos:

```text
obtenerCursosDisponibles()
calcularProgreso()
usuarioActual
cursoAsignado
moduloAnterior
```

## Base de datos

Usar:

```text
snake_case
```

Ejemplos:

```text
grados_academicos
asignaciones_cursos
progreso_modulos_usuario
correo_electronico
grado_academico_id
```

## JSON

Las claves JSON deberán utilizar:

```text
snake_case
```

y estarán en español.

Ejemplo:

```json
{
    "tipo_actividad": "falso_verdadero",
    "respuesta_correcta": true
}
```

---

# 4. Stack tecnológico obligatorio

El proyecto utilizará:

* Laravel 13
* PHP `^8.4`
* PostgreSQL
* Filament 5
* Livewire 4
* Flux UI gratuito
* Tailwind CSS 4
* Alpine.js proporcionado por Livewire
* DomPDF
* Vite
* Workbox
* `vite-plugin-pwa`

No sustituir estas tecnologías sin autorización explícita.

No agregar nuevas dependencias Composer o NPM sin justificar previamente su necesidad.

---

# 5. Alpine.js

Livewire proporciona Alpine.js.

No cargar Alpine.js manualmente una segunda vez.

Antes de agregar scripts externos relacionados con Alpine:

1. verificar la configuración actual;
2. comprobar que Alpine no se encuentre ya disponible;
3. evitar instancias duplicadas.

---

# 6. Configuración regional

La aplicación utilizará:

```text
Idioma: español
Zona horaria: America/Guatemala
```

Configurar Laravel de forma consistente con estos valores.

Los textos visibles al usuario deberán estar siempre en español.

---

# 7. Arquitectura general

La aplicación estará dividida conceptualmente en dos áreas principales:

1. Portal del estudiante.
2. BackOffice administrativo.

---

# 8. Portal del estudiante

El portal estudiantil se implementará principalmente mediante:

* Laravel;
* Blade;
* Livewire;
* Flux UI;
* Tailwind CSS;
* Alpine.js cuando sea necesario.

Las páginas principales serán componentes Livewire cuando exista lógica interactiva relevante.

Evitar colocar lógica de negocio compleja directamente en Blade.

---

# 9. BackOffice

El BackOffice administrativo utilizará Filament 5.

Ruta principal:

```text
/admin
```

Solo podrán ingresar usuarios:

* con rol `superadministrador`;
* activos.

La autorización deberá comprobarse en servidor.

Ocultar un elemento visual no constituye una medida de seguridad suficiente.

---

# 10. Servicios de dominio

La lógica importante deberá centralizarse cuando sea razonable mediante servicios específicos.

Ejemplos:

```text
ServicioAccesoCursos
ServicioProgreso
ServicioCalificacion
ServicioDiplomas
ServicioDesbloqueo
```

Estos servicios podrán encargarse de:

* disponibilidad de cursos;
* asignaciones;
* progreso;
* desbloqueo de módulos;
* desbloqueo de niveles;
* calificación;
* finalización;
* generación de diplomas.

Evitar duplicar estas reglas entre distintos componentes Livewire o recursos Filament.

---

# 11. Autorización

Utilizar según corresponda:

* Policies;
* middleware;
* validaciones del servidor;
* comprobaciones de pertenencia;
* permisos de acceso.

Nunca confiar únicamente en:

* botones ocultos;
* campos deshabilitados;
* JavaScript;
* propiedades visuales.

---

# 12. Persistencia

La persistencia oficial será:

* PostgreSQL;
* Eloquent ORM.

Utilizar relaciones Eloquent cuando sea razonable.

Evitar SQL manual innecesario.

Se permite utilizar características específicas de PostgreSQL cuando sean necesarias, especialmente:

* JSONB;
* índices parciales;
* restricciones;
* consultas específicas de PostgreSQL.

---

# 13. Convenciones Eloquent

Los modelos utilizarán:

```php
protected $guarded = [];
```

Esta decisión obliga a validar estrictamente los datos antes de persistirlos.

No utilizar asignación masiva con información proveniente directamente del cliente sin validación previa.

La validación podrá implementarse mediante:

* componentes Livewire;
* Form Requests;
* formularios Filament;
* validadores explícitos.

---

# 14. Timestamps personalizados

Todas las tablas propias utilizarán:

```text
creado_en
actualizado_en
```

Los modelos deberán declarar:

```php
public const CREATED_AT = 'creado_en';
public const UPDATED_AT = 'actualizado_en';
```

No utilizar `created_at` y `updated_at` en las tablas propias del dominio.

---

# 15. Modelos principales

Los modelos principales serán:

```text
Colegio
GradoAcademico
Usuario
Curso
Nivel
Modulo
Capsula
AsignacionCurso
IntentoActividad
ProgresoModuloUsuario
Diploma
```

Cada modelo deberá declarar explícitamente su tabla.

Ejemplo:

```php
protected $table = 'colegios';
```

Las relaciones deberán especificar explícitamente las claves españolas cuando sea necesario.

Ejemplo conceptual:

```php
return $this->belongsTo(Colegio::class, 'colegio_id');
```

---

# 16. Estructura institucional

## Tabla `colegios`

Campos:

```text
id
nombre
codigo
activo
creado_en
actualizado_en
```

Reglas:

* `codigo` será único.

---

## Tabla `grados_academicos`

Campos:

```text
id
nombre
codigo
orden
activo
creado_en
actualizado_en
```

Reglas:

* `codigo` será único.

Los grados académicos son un catálogo global.

---

# 17. Usuarios

## Tabla `usuarios`

Campos:

```text
id
nombre
correo_electronico
contrasena
rol
colegio_id
grado_academico_id
activo
token_recordatorio
creado_en
actualizado_en
```

`correo_electronico` será único.

Los únicos roles permitidos serán:

```text
superadministrador
estudiante
```

## Estudiantes

Para un estudiante:

```text
colegio_id
grado_academico_id
```

serán obligatorios.

## Superadministradores

Para un superadministrador:

```text
colegio_id
grado_academico_id
```

serán nulos.

No existirán inicialmente:

* docentes;
* administradores escolares;
* autorregistro;
* recuperación de contraseña.

---

# 18. Autenticación del modelo Usuario

El modelo utilizado para autenticación será:

```text
Usuario
```

Debe adaptarse correctamente para utilizar:

```text
correo_electronico
contrasena
token_recordatorio
```

No asumir automáticamente los nombres predeterminados:

```text
email
password
remember_token
```

Configurar explícitamente Laravel donde sea necesario.

---

# 19. Catálogo educativo

## Tabla `cursos`

Campos:

```text
id
titulo
descripcion
ruta_imagen
titulo_bienvenida
contenido_bienvenida
orden
publicado
creado_en
actualizado_en
```

`titulo_bienvenida` y `contenido_bienvenida` permitirán personalizar la pantalla de inicio de cada curso.

---

## Tabla `niveles`

Campos:

```text
id
curso_id
titulo
descripcion
ruta_imagen
orden
publicado
creado_en
actualizado_en
```

Relación:

```text
Curso
└── muchos Niveles
```

---

## Tabla `modulos`

Campos:

```text
id
nivel_id
titulo
descripcion
ruta_imagen
orden
bloques_contenido
actividades
mensaje_cierre
publicado
creado_en
actualizado_en
```

Tipos PostgreSQL:

```text
bloques_contenido → JSONB
actividades → JSONB
```

El modelo `Modulo` deberá convertir ambos campos a arreglos mediante casts.

---

# 20. Cápsulas

## Tabla `capsulas`

Campos:

```text
id
modulo_id
titulo
contenido
ruta_imagen
orden
activo
creado_en
actualizado_en
```

Reglas:

* `modulo_id` es obligatorio;
* `titulo` es opcional;
* toda cápsula pertenece a un módulo.

---

# 21. Asignaciones de cursos

## Tabla `asignaciones_cursos`

Campos:

```text
id
curso_id
colegio_id
grado_academico_id
activo
inicia_en
finaliza_en
creado_en
actualizado_en
```

`grado_academico_id` será nullable.

Cuando:

```text
grado_academico_id = null
```

la asignación aplica a todo el colegio.

Cuando tenga un grado:

```text
grado_academico_id = X
```

aplica exclusivamente a ese grado.

Utilizar índices únicos parciales de PostgreSQL para impedir duplicados en:

* asignaciones generales;
* asignaciones específicas.

Validar que:

```text
finaliza_en >= inicia_en
```

cuando ambos valores existan.

---

# 22. Intentos de actividades

## Tabla `intentos_actividades`

Campos:

```text
id
usuario_id
modulo_id
actividad_uuid
tipo_actividad
numero_intento
respuesta
correcta
respondido_en
creado_en
actualizado_en
```

`respuesta` utilizará JSONB.

`correcta` será nullable.

Cada intento debe conservarse independientemente.

Nunca sobrescribir un intento anterior.

---

# 23. Progreso

## Tabla `progreso_modulos_usuario`

Campos:

```text
id
usuario_id
modulo_id
completado_en
creado_en
actualizado_en
```

Crear una restricción única para:

```text
usuario_id + modulo_id
```

La finalización deberá ser idempotente.

Puede utilizarse:

```php
firstOrCreate()
```

cuando corresponda.

---

# 24. Integridad histórica

Nunca eliminar información histórica asociada a:

* intentos;
* progreso;
* diplomas.

Cuando una entidad ya no deba estar disponible, preferir:

* `activo = false`;
* `publicado = false`;
* archivado lógico;

antes que eliminar información histórica relacionada.

El progreso ya obtenido deberá conservarse aunque posteriormente se edite un módulo.

Los diplomas emitidos deberán conservarse aunque posteriormente cambie el contenido o la estructura del curso.

---

# 25. Contrato JSONB de bloques de contenido

`bloques_contenido` almacenará bloques estructurados.

Tipo inicial:

```text
tarjeta
```

Cada tarjeta tendrá:

```text
uuid
titulo
contenido
ruta_imagen
```

Los UUID deben permanecer estables después de crear el contenido.

---

# 26. Tipos de actividades

`actividades` podrá contener:

```text
falso_verdadero
opcion_multiple
respuesta_directa
ordenacion
clasificacion
```

Cada actividad deberá tener un UUID estable.

---

# 27. Falso o verdadero

Estructura conceptual:

```text
uuid
tipo
pregunta
respuesta_correcta
```

La respuesta correcta será booleana.

La calificación siempre se realizará en servidor.

---

# 28. Opción múltiple

Estructura conceptual:

```text
uuid
tipo
pregunta
opciones
opcion_correcta_uuid
```

Cada opción tendrá:

```text
uuid
texto
```

Reglas:

* mínimo dos opciones;
* exactamente una opción correcta;
* UUID estable para cada opción.

---

# 29. Respuesta directa

Estructura conceptual:

```text
uuid
tipo
pregunta
```

No tendrá:

```text
respuesta_correcta
```

No tendrá calificación automática.

Se considerará realizada cuando el estudiante envíe una respuesta válida.

---

# 30. Ordenación

Estructura conceptual:

```text
uuid
tipo
instruccion
elementos
```

Cada elemento tendrá:

```text
uuid
texto
posicion
```

Debe contener como mínimo dos elementos.

---

# 31. Clasificación

Estructura conceptual:

```text
uuid
tipo
instruccion
categorias
```

Cada categoría tendrá:

```text
uuid
nombre
elementos
```

Cada elemento deberá contar con UUID estable.

Debe existir como mínimo:

```text
2 categorías
```

---

# 32. Seguridad de actividades

Las respuestas correctas nunca deberán exponerse innecesariamente al navegador.

No colocar respuestas correctas en:

* propiedades públicas Livewire;
* atributos HTML;
* JavaScript;
* Alpine.js;
* datasets;
* formularios ocultos;
* JSON enviado al cliente antes de responder.

La respuesta del estudiante deberá enviarse al servidor.

El servidor deberá:

1. localizar la actividad actual;
2. obtener la respuesta correcta desde PostgreSQL;
3. comparar la respuesta;
4. registrar el intento;
5. devolver únicamente el resultado necesario.

---

# 33. Relaciones Eloquent

Configurar todas las relaciones necesarias en ambos sentidos.

Ejemplos conceptuales:

```text
Colegio → Usuarios
Colegio → AsignacionesCurso
Colegio → Diplomas

GradoAcademico → Usuarios
GradoAcademico → AsignacionesCurso

Curso → Niveles
Curso → AsignacionesCurso
Curso → Diplomas

Nivel → Curso
Nivel → Modulos

Modulo → Nivel
Modulo → Capsulas
Modulo → IntentosActividad
Modulo → ProgresoModuloUsuario

Usuario → Colegio
Usuario → GradoAcademico
Usuario → IntentosActividad
Usuario → ProgresoModuloUsuario
Usuario → Diplomas

Diploma → Usuario
Diploma → Curso
```

Utilizar eager loading para evitar consultas N+1.

---

# 34. Casts

Configurar casts adecuados para:

* booleanos;
* fechas;
* JSONB;
* enums;
* timestamps.

Especialmente:

```text
publicado
activo
bloques_contenido
actividades
inicia_en
finaliza_en
respondido_en
completado_en
emitido_en
```

---

# 35. Scopes

Crear scopes reutilizables cuando sean necesarios.

Ejemplos:

```text
publicados()
activos()
vigentes()
accesiblesPara()
```

Evitar repetir condiciones complejas en múltiples componentes.

---

# 36. Factories y seeders

Crear factories para las entidades principales cuando sea necesario para pruebas.

Podrá existir un seeder de demostración.

No incluir:

* credenciales reales;
* contraseñas administrativas fijas de producción;
* tokens reales;
* secretos.

Las contraseñas de datos demostrativos deberán generarse de manera segura y documentarse únicamente cuando sean estrictamente necesarias para desarrollo local.

---

# 37. Filament 5

Crear el panel administrativo en:

```text
/admin
```

Los recursos principales serán:

```text
ColegioResource
GradoAcademicoResource
CursoResource
NivelResource
UsuarioResource
AsignacionCursoResource
ModuloResource
IntentoActividadResource
DiplomaResource
```

`IntentoActividadResource` y `DiplomaResource` serán de solo lectura.

Toda la navegación, etiquetas, validaciones, formularios y columnas visibles deberán estar en español.

---

# 38. ColegioResource

Administrará:

* nombre;
* código;
* estado.

No permitir códigos duplicados.

---

# 39. GradoAcademicoResource

Administrará:

* nombre;
* código;
* orden;
* estado.

---

# 40. CursoResource

Administrará:

* título;
* descripción;
* imagen;
* título de bienvenida;
* contenido de bienvenida;
* orden;
* publicación.

Los campos `titulo_bienvenida` y `contenido_bienvenida` definirán el contenido mostrado en la pantalla de inicio del curso.

---

# 41. NivelResource

Administrará:

* curso;
* título;
* descripción;
* imagen;
* orden;
* publicación.

El curso deberá seleccionarse mediante la relación correspondiente usando:

```text
curso_id
```

---

# 42. UsuarioResource

Administrará:

* nombre;
* correo electrónico;
* rol;
* colegio;
* grado académico;
* estado;
* contraseña.

Las reglas de colegio y grado deberán variar según el rol.

Una nueva contraseña deberá procesarse mediante:

```php
Hash::make()
```

solamente cuando se haya proporcionado una contraseña nueva.

Evitar doble hash.

---

# 43. AsignacionCursoResource

Administrará:

* curso;
* colegio;
* grado;
* estado;
* fecha inicial;
* fecha final.

Debe impedir asignaciones duplicadas y fechas inválidas.

---

# 44. ModuloResource

Utilizar Builder cuando sea apropiado para administrar:

```text
bloques_contenido
actividades
```

## Bloques

Permitir inicialmente:

```text
tarjeta
```

con:

* título;
* imagen;
* RichEditor.

## Actividades

Permitir:

* falso o verdadero;
* opción múltiple;
* respuesta directa;
* ordenación;
* clasificación.

Los elementos deberán recibir UUID estables automáticamente.

---

# 45. Cápsulas en Filament

Gestionar cápsulas mediante un `RelationManager` asociado al módulo.

Nombre sugerido:

```text
CapsulasRelationManager
```

---

# 46. Intentos en BackOffice

Proporcionar una vista o recurso de solo lectura para consultar intentos.

Permitir filtrado cuando corresponda por:

* colegio;
* estudiante;
* curso;
* nivel;
* módulo;
* tipo de actividad.

No permitir modificar respuestas históricas desde el BackOffice.

`DiplomaResource` permitirá consultar y descargar diplomas, pero no modificar sus datos históricos.

---

# 47. Portal estudiantil: Login

Crear:

```text
/login
```

como página Livewire.

Campos:

```text
correo_electronico
contrasena
```

Solo estudiantes activos podrán autenticarse en este portal.

La autenticación deberá realizarse en servidor.

Después de una autenticación válida:

1. regenerar la sesión;
2. redirigir a `/dashboard`.

Agregar:

* rate limiting;
* mensajes genéricos para credenciales incorrectas;
* protección contra enumeración innecesaria de usuarios.

---

# 48. Diseño del Login

La referencia visual oficial se encuentra en:

```text
docs/design/login/
```

Antes de implementar el login:

1. revisar todos los archivos de la carpeta;
2. identificar cuál imagen representa el diseño objetivo;
3. identificar assets separados;
4. identificar tipografía;
5. revisar componentes existentes.

Recrear el diseño de referencia lo más fielmente posible.

Respetar:

* distribución;
* proporciones;
* colores;
* tipografía;
* tamaños;
* espaciados;
* bordes;
* sombras;
* iconos;
* jerarquía visual.

En escritorio deberá conservarse la composición de dos columnas mostrada en la referencia.

En móvil deberá adaptarse mediante apilamiento u otra adaptación equivalente que mantenga correctamente la experiencia.

No inventar un diseño diferente cuando la referencia pueda implementarse correctamente.

---

# 49. Dashboard

Ruta principal:

```text
/dashboard
```

Mostrar cursos publicados accesibles para el estudiante.

Un curso será accesible cuando exista una asignación:

* activa;
* vigente;
* correspondiente al colegio del estudiante;

y además:

```text
grado_academico_id IS NULL
```

o:

```text
grado_academico_id = grado del estudiante
```

Evitar duplicar cursos cuando existan simultáneamente asignaciones:

* generales;
* específicas.

Mostrar cuando corresponda:

* tarjetas de cursos;
* progreso;
* niveles;
* módulos;
* estados;
* bloqueo y desbloqueo.

Los cursos completados con diploma emitido deberán ofrecer una descarga autorizada desde el Dashboard.

---

# 50. Cálculo de progreso

El porcentaje de progreso deberá calcularse utilizando únicamente módulos:

* publicados;
* pertenecientes al curso o nivel;
* completados por el usuario.

La lógica de progreso deberá centralizarse y no duplicarse innecesariamente en las vistas.

---

# 51. Pantalla de inicio del curso

La referencia visual se encuentra en:

```text
docs/design/iniciomodulo/
```

Esta pantalla se mostrará después de seleccionar un curso y antes de visualizar los niveles.

Mostrará el contenido personalizado de:

```text
titulo_bienvenida
contenido_bienvenida
```

Debe existir una acción equivalente a:

```text
Comenzar
```

que lleve a la pantalla de niveles.

Recrear el diseño de referencia lo más fielmente posible.

---

# 52. Pantalla de niveles

La referencia visual se encuentra en:

```text
docs/design/aprende/
```

Esta pantalla se mostrará después de presionar:

```text
Comenzar
```

desde la pantalla de inicio del curso.

Existen referencias visuales para diferentes estados de progreso.

Codex deberá revisar todas las referencias antes de implementar.

La interfaz deberá reflejar correctamente:

* módulos bloqueados;
* módulos disponibles;
* módulos completados;
* nivel completado;
* progreso general.

---

# 53. Visor de módulos

Crear una ruta equivalente a:

```text
/modulos/{modulo}
```

La autorización deberá verificar:

1. usuario autenticado;
2. estudiante activo;
3. curso asignado;
4. vigencia;
5. secuencia;
6. módulo habilitado.

No permitir acceder simplemente escribiendo manualmente la URL de un módulo bloqueado.

---

# 54. Flujo interno del módulo

El flujo será:

```text
Contenido
↓
Cápsulas
↓
Actividades
↓
Mensaje de cierre
↓
Finalización
```

Cuando corresponda, reanudar al estudiante desde la primera actividad pendiente.

---

# 55. Reintentos

Los reintentos serán ilimitados.

Cada intento deberá generar un nuevo registro.

No eliminar intentos fallidos.

---

# 56. Reglas de aprobación

Para:

```text
falso_verdadero
opcion_multiple
ordenacion
clasificacion
```

la actividad deberá completarse correctamente.

La respuesta directa se considera realizada cuando se envía correctamente.

La finalización completa del módulo dependerá de haber cumplido todas las actividades requeridas.

---

# 57. Secuencia de módulos

La secuencia será estricta.

Un módulo se habilitará cuando:

* sea el primero disponible;

o:

* el módulo anterior esté completado.

No permitir saltar módulos mediante manipulación de URL o solicitudes.

---

# 58. Secuencia de niveles

Cuando todos los módulos publicados de un nivel estén completados, podrá habilitarse el siguiente nivel según las reglas definidas.

Mantener la secuencia de forma consistente.

---

# 59. Ordenación y clasificación

Estas actividades podrán utilizar:

* Alpine.js;
* Pointer Events.

También deberán funcionar correctamente:

* en dispositivos táctiles;
* mediante una alternativa accesible cuando sea necesario.

No implementar una interacción que solamente funcione con mouse.

---

# 60. Diplomas

Utilizar DomPDF.

## Tabla `diplomas`

Campos:

```text
id
usuario_id
curso_id
nombre_estudiante
nombre_colegio
titulo_curso
emitido_en
identificador_interno
creado_en
actualizado_en
```

Reglas:

* `identificador_interno` será único;
* existirá una restricción única para `usuario_id + curso_id`;
* los nombres y el título serán instantáneas históricas del momento de emisión;
* la emisión será única e idempotente por estudiante y curso;
* un diploma emitido se conservará aunque después cambie el contenido del curso.

El diploma únicamente podrá generarse cuando el estudiante haya cumplido las condiciones necesarias para completar el curso.

La lógica de elegibilidad deberá validarse en servidor.

El documento mostrará:

* nombre del estudiante;
* colegio;
* curso;
* fecha de emisión.

Los archivos privados no deberán exponerse mediante URLs públicas inseguras.

---

# 61. Almacenamiento

Inicialmente se utilizará almacenamiento local configurable mediante Laravel.

Debe ser posible migrar posteriormente a servicios como S3 sin rehacer completamente la lógica de negocio.

Separar correctamente:

* archivos públicos;
* archivos privados.

---

# 62. PWA

Implementar la PWA utilizando:

* Vite;
* Workbox;
* `vite-plugin-pwa`.

La estrategia principal será:

```text
online-first
```

Implementar fallback offline cuando corresponda.

La PWA nunca deberá almacenar de forma insegura:

* credenciales;
* respuestas correctas;
* información privada;
* contenido administrativo sensible.

---

# 63. Recursos visuales

Los recursos gráficos de referencia se encuentran principalmente en:

```text
docs/
docs/design/
```

Antes de crear un recurso nuevo:

1. buscar si ya existe;
2. reutilizarlo cuando corresponda;
3. evitar duplicarlo innecesariamente.

---

# 64. Tipografía

La tipografía oficial del proyecto estará almacenada dentro del repositorio.

Antes de implementar una interfaz:

1. localizar los archivos de tipografía disponibles;
2. identificar sus pesos;
3. identificar sus estilos;
4. identificar formatos disponibles.

Para web priorizar:

```text
.woff2
```

No utilizar Google Fonts ni otra tipografía externa si existe una tipografía oficial local.

No sustituir la tipografía oficial sin autorización.

Si la fuente todavía no está configurada, integrarla correctamente mediante `@font-face` u otra solución compatible con el stack existente.

---

# 65. Diseño visual

Las referencias de `docs/design/` se consideran la fuente visual principal.

Cuando se solicite recrear una pantalla:

* analizar primero la imagen;
* implementar después.

La intención será reproducir el diseño lo más fielmente posible.

Respetar:

* layout;
* colores;
* tipografía;
* tamaños;
* espacios;
* iconografía;
* bordes;
* radios;
* sombras;
* proporciones;
* comportamiento responsive.

No reinterpretar significativamente el diseño salvo por:

* accesibilidad;
* responsive;
* limitaciones técnicas justificadas.

---

# 66. Tailwind CSS

Priorizar Tailwind CSS para estilos.

Evitar CSS personalizado si Tailwind puede resolver correctamente la necesidad.

Puede utilizarse CSS personalizado cuando:

* sea necesario para la tipografía;
* exista una animación compleja;
* Tailwind no sea suficiente;
* exista una necesidad técnica justificada.

---

# 67. Flux UI

Utilizar Flux UI gratuito cuando exista un componente apropiado.

No introducir componentes de pago.

No reemplazar innecesariamente componentes existentes que ya funcionen correctamente.

---

# 68. Responsive

Todas las pantallas estudiantiles deberán funcionar correctamente en:

1. móvil;
2. tablet;
3. escritorio.

La prioridad será mobile-first cuando resulte apropiado.

No considerar finalizada una interfaz si únicamente funciona correctamente en escritorio.

---

# 69. Accesibilidad

Considerar como mínimo:

* labels;
* foco visible;
* navegación mediante teclado;
* contraste;
* mensajes de error comprensibles;
* botones utilizables mediante dispositivos táctiles;
* atributos semánticos cuando corresponda.

---

# 70. Rendimiento

Evitar:

* consultas N+1;
* cargar relaciones innecesarias;
* consultas repetidas;
* duplicación de datos;
* assets excesivamente pesados;
* lógica costosa ejecutándose múltiples veces en Livewire.

Utilizar eager loading cuando sea necesario.

---

# 71. Transacciones

Utilizar transacciones cuando varias operaciones dependan entre sí.

Ejemplos:

* registrar intento y actualizar progreso;
* completar curso y generar datos relacionados;
* operaciones administrativas compuestas.

Evitar estados parcialmente persistidos.

---

# 72. Estructura esperada

Mantener las convenciones naturales de Laravel.

La aplicación podrá contener conceptualmente:

```text
app/
├── Filament/
├── Livewire/
├── Models/
├── Policies/
├── Services/
└── ...

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/
├── js/
└── views/

public/

tests/

docs/
└── design/
```

Los nombres de directorios estándar de Laravel no deberán traducirse.

Las clases propias contenidas dentro de ellos estarán en español.

---

# 73. Variables de entorno

Las configuraciones sensibles deberán permanecer en `.env`.

Nunca incluir credenciales reales en Git.

Mantener `.env.example` actualizado cuando se incorporen variables necesarias.

Las configuraciones podrán incluir:

* aplicación;
* PostgreSQL;
* sesiones;
* caché;
* filesystem;
* correo;
* diplomas;
* PWA.

Nunca colocar valores secretos reales en documentación.

---

# 74. Seguridad de credenciales

No exponer:

* contraseñas;
* tokens;
* API keys;
* claves privadas;
* secretos;
* credenciales PostgreSQL.

No subir `.env` al repositorio.

No imprimir secretos en logs.

---

# 75. Etapas obligatorias del desarrollo

El proyecto se desarrollará en las siguientes etapas:

## Etapa 1

Scaffolding y dependencias.

## Etapa 2

Base de datos, modelos, factories y seeders.

## Etapa 3

Servicios, autorización y progreso.

## Etapa 4

BackOffice con Filament.

## Etapa 5

Login y layout del estudiante.

## Etapa 6

Dashboard, bienvenida del curso y niveles.

## Etapa 7

Visor de módulos y motor de actividades.

## Etapa 8

Diplomas.

## Etapa 9

PWA.

## Etapa 10

QA integral.

---

# 76. Control obligatorio por etapas

No mezclar innecesariamente funcionalidades de etapas futuras.

Mientras se trabaja en una etapa:

* limitar los cambios a dicha etapa;
* implementar únicamente dependencias estrictamente necesarias;
* probar antes de continuar;
* corregir errores antes de avanzar.

Codex no deberá avanzar automáticamente a la siguiente etapa.

Se deberá esperar una nueva instrucción del usuario.

---

# 77. Requisitos de cada etapa

Cada etapa deberá:

1. tener alcance definido;
2. incluir pruebas relacionadas;
3. ejecutar migraciones cuando corresponda;
4. verificar PostgreSQL cuando corresponda;
5. ejecutar compilación frontend cuando corresponda;
6. pasar Laravel Pint;
7. no finalizar con pruebas fallidas;
8. no dejar errores conocidos relacionados con el cambio;
9. evitar modificaciones fuera del alcance.

---

# 78. Forma de trabajo de Codex

Antes de modificar código, Codex deberá:

1. leer este `AGENTS.md`;
2. inspeccionar los archivos relacionados;
3. comprender la implementación existente;
4. identificar relaciones y dependencias;
5. revisar referencias visuales cuando corresponda;
6. comprobar si existe código reutilizable;
7. evaluar posibles efectos secundarios.

No asumir una estructura que pueda comprobarse directamente en el repositorio.

---

# 79. Antes de cambios grandes

Para cambios de alcance considerable, Codex deberá presentar primero un resumen breve indicando:

* qué encontró;
* qué archivos están involucrados;
* cuál es la solución propuesta;
* qué archivos planea crear;
* qué archivos planea modificar;
* riesgos relevantes.

Si el usuario indicó explícitamente que no desea cambios todavía, no modificar código.

---

# 80. Durante la implementación

Codex deberá:

* modificar únicamente archivos necesarios;
* respetar las convenciones del repositorio;
* reutilizar código existente;
* evitar duplicación;
* mantener lógica compleja fuera de Blade;
* utilizar tipado cuando sea razonable;
* utilizar relaciones Eloquent;
* aplicar autorización en servidor;
* utilizar transacciones cuando sea necesario;
* evitar N+1;
* respetar el diseño establecido.

---

# 81. No realizar cambios innecesarios

No realizar refactors globales no solicitados.

No cambiar sin una razón relacionada directamente con la tarea:

* arquitectura;
* nombres;
* tecnologías;
* estructura de base de datos;
* contratos JSON;
* flujo funcional;
* diseño general.

Si se detecta una mejora no relacionada, informar al usuario en lugar de implementarla automáticamente.

---

# 82. Pruebas

Crear o actualizar pruebas cuando una funcionalidad nueva lo requiera.

Cubrir especialmente:

* autenticación;
* autorización;
* asignaciones;
* progreso;
* desbloqueo;
* intentos;
* actividades;
* seguridad;
* finalización;
* acceso administrativo;
* emisión y descarga de diplomas.

---

# 83. Pruebas PostgreSQL

Las funcionalidades dependientes de PostgreSQL deberán probarse utilizando PostgreSQL.

Especialmente:

* JSONB;
* índices parciales;
* restricciones;
* comportamiento de asignaciones.

No asumir que SQLite reproduce correctamente características propias de PostgreSQL.

---

# 84. Pruebas de autenticación

Cubrir como mínimo:

* visitante;
* estudiante activo;
* estudiante inactivo;
* superadministrador activo;
* superadministrador inactivo.

Verificar que cada tipo acceda únicamente a las áreas permitidas.

---

# 85. Pruebas de asignaciones

Cubrir:

* asignación general;
* asignación específica;
* asignación futura;
* asignación vencida;
* asignación inactiva;
* combinación de asignación general y específica.

---

# 86. Pruebas de progreso

Cubrir:

* primer módulo;
* módulo bloqueado;
* desbloqueo;
* reanudación;
* finalización;
* finalización repetida;
* cambio posterior del contenido;
* progreso entre niveles;
* emisión idempotente del diploma;
* conservación del diploma tras cambios posteriores del curso.

---

# 87. Pruebas de actividades

Cubrir los cinco tipos:

```text
falso_verdadero
opcion_multiple
respuesta_directa
ordenacion
clasificacion
```

Verificar:

* respuestas correctas;
* respuestas incorrectas;
* reintentos;
* almacenamiento;
* UUID;
* secuencia.

---

# 88. Seguridad Livewire

Verificar mediante pruebas cuando sea posible que las respuestas correctas no aparezcan en:

* estado público del componente;
* HTML;
* atributos;
* datos enviados innecesariamente al navegador.

---

# 89. Pruebas visuales

Revisar manualmente las interfaces principales en:

* móvil;
* tablet;
* escritorio.

Especialmente:

* login;
* dashboard;
* inicio de curso;
* niveles;
* visor de módulos.

Compararlas con las referencias de `docs/design/`.

Los diplomas deberán renderizarse y revisarse visualmente para evitar texto cortado, solapamientos o defectos de tipografía.

---

# 90. Laravel Pint

Después de modificaciones PHP relevantes ejecutar:

```bash
./vendor/bin/pint
```

El código entregado deberá respetar el formato del proyecto.

---

# 91. Compilación frontend

Cuando existan modificaciones relacionadas con frontend ejecutar la compilación correspondiente.

Por ejemplo:

```bash
npm run build
```

No considerar finalizado un cambio frontend si la compilación presenta errores.

---

# 92. Migraciones

Cuando se agreguen o modifiquen migraciones:

* verificar sintaxis;
* ejecutar sobre PostgreSQL;
* revisar restricciones;
* revisar claves foráneas;
* revisar índices.

No modificar migraciones históricas ya utilizadas en entornos compartidos si corresponde crear una nueva migración.

---

# 93. Comandos habituales

Instalación PHP:

```bash
composer install
```

Instalación frontend:

```bash
npm install
```

Generar clave:

```bash
php artisan key:generate
```

Migraciones:

```bash
php artisan migrate
```

Pruebas:

```bash
php artisan test
```

Formato:

```bash
./vendor/bin/pint
```

Compilación:

```bash
npm run build
```

Desarrollo frontend:

```bash
npm run dev
```

Servidor Laravel cuando corresponda:

```bash
php artisan serve
```

No asumir que todos los entornos necesitan `php artisan serve`; herramientas como Laravel Herd pueden gestionar el servidor local.

---

# 94. Git

No modificar ni eliminar historial Git salvo instrucción explícita.

No ejecutar automáticamente:

```text
git reset --hard
git push --force
git clean
```

sin autorización explícita.

No eliminar cambios locales que no hayan sido creados por Codex.

---

# 95. Archivos que no deben versionarse

No agregar al repositorio:

```text
.env
vendor/
node_modules/
```

salvo una decisión explícita y justificada del proyecto.

Sí deben mantenerse cuando corresponda:

```text
composer.json
composer.lock
package.json
package-lock.json
.env.example
AGENTS.md
docs/
```

---

# 96. Portabilidad entre equipos

No utilizar rutas absolutas personales dentro de documentación o código.

Evitar:

```text
/Users/nombre/...
C:\Users\nombre\...
```

Utilizar rutas relativas al repositorio.

Ejemplo correcto:

```text
docs/design/login/
resources/fonts/
```

Esto permite trabajar con el mismo repositorio en Windows, macOS y otros entornos.

---

# 97. Finalización de una tarea

Antes de indicar que una tarea está terminada, Codex deberá:

1. revisar los cambios realizados;
2. revisar archivos modificados;
3. ejecutar las pruebas relacionadas cuando sea posible;
4. ejecutar Laravel Pint cuando corresponda;
5. ejecutar migraciones cuando corresponda;
6. ejecutar compilación frontend cuando corresponda;
7. comprobar errores conocidos;
8. verificar el alcance;
9. informar qué archivos fueron creados;
10. informar qué archivos fueron modificados.

No afirmar que una prueba o comando se ejecutó si realmente no se ejecutó.

Si un comando no pudo ejecutarse, explicar claramente la razón.

---

# 98. Criterios de aceptación generales

Una funcionalidad no deberá considerarse finalizada si:

* contiene errores conocidos relacionados con la tarea;
* rompe pruebas existentes;
* rompe la compilación;
* incumple seguridad;
* incumple autorización;
* expone respuestas correctas;
* rompe el flujo secuencial;
* rompe el diseño responsive;
* utiliza nombres de dominio en inglés;
* introduce credenciales reales;
* modifica funcionalidades fuera del alcance sin justificación.

---

# 99. Supuestos definitivos

Los siguientes puntos se consideran decisiones actuales del proyecto:

* El idioma del dominio es español.
* Las clases propias estarán en español.
* Los modelos estarán en español.
* Los recursos Filament estarán en español.
* Los servicios estarán en español.
* Las variables y métodos propios estarán en español.
* Las tablas estarán en español.
* Las columnas estarán en español.
* Las claves JSON estarán en español.
* La interfaz estará en español.
* No se utilizarán tildes ni `ñ` en identificadores.
* Los nombres internos estándar de Laravel y sus dependencias no se traducirán.
* Solo existirán los roles `superadministrador` y `estudiante`.
* No habrá autorregistro.
* No habrá recuperación de contraseña inicialmente.
* No existirán docentes.
* No existirán administradores escolares.
* Los grados académicos serán un catálogo global.
* Las cápsulas pertenecerán siempre a un módulo.
* Los reintentos serán ilimitados.
* La respuesta directa no tendrá calificación automática.
* El progreso será secuencial.
* El progreso histórico no deberá eliminarse.
* Los diplomas se emitirán una sola vez por estudiante y curso.
* Los diplomas conservarán instantáneas históricas y no se revocarán por cambios posteriores del curso.
* El almacenamiento inicial será local.
* Se permitirá migrar posteriormente a S3.
* PostgreSQL será la base de datos oficial.
* La PWA utilizará estrategia online-first.
* La tipografía oficial será local.
* Las referencias visuales estarán en `docs/design/`.
* El valor `#F0F0F`, si aparece en documentación anterior, deberá interpretarse como `#F0F0F0`.

---

# 100. Prioridad de instrucciones

Este archivo establece las reglas permanentes generales del repositorio.

Si una instrucción explícita y reciente del usuario contradice este archivo para una tarea concreta, Codex deberá:

1. identificar el conflicto;
2. seguir la instrucción explícita del usuario cuando sea inequívoca;
3. evitar modificar otras reglas no relacionadas.

Cuando exista una contradicción importante de arquitectura, seguridad o integridad de datos, deberá informarse antes de realizar un cambio destructivo.
