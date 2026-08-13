# EduTech KATO-KI

Base técnica de la plataforma B2B de educación financiera de KATO-KI.

El repositorio se desarrolla por etapas. Actualmente contiene únicamente el scaffolding y las dependencias de la Etapa 1; todavía no incluye modelos, migraciones ni funcionalidades del dominio.

## Requisitos

- PHP 8.4 o superior.
- Composer 2.
- Node.js y npm.
- PostgreSQL administrado localmente mediante DBngin o una instancia PostgreSQL equivalente.
- Laravel Herd es opcional para servir el proyecto localmente.

## Stack base

- Laravel 13.
- Livewire 4.
- Filament 5.
- Flux UI gratuito.
- Tailwind CSS 4.
- Vite.
- DomPDF.
- Workbox y `vite-plugin-pwa` como base para la futura PWA.

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
```

Completa en `.env` la conexión de desarrollo suministrada por DBngin. No reutilices una base de datos ajena al proyecto:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Cuando exista una base de desarrollo identificada y configurada:

```bash
php artisan migrate
```

## Base de datos de pruebas

Copia `.env.testing.example` como `.env.testing` y completa una base PostgreSQL exclusiva para pruebas. Nunca utilices la base de desarrollo o una base existente no identificada para ejecutar pruebas que modifiquen datos.

## Ejecución

Con Laravel Herd no es necesario ejecutar un servidor PHP manual. Para usar el servidor integrado:

```bash
php artisan serve
```

Para compilar los assets durante el desarrollo:

```bash
npm run dev
```

## Validación

```bash
composer validate
php artisan about
php artisan test
./vendor/bin/pint
npm run build
```

Consulta `AGENTS.md` antes de realizar cualquier cambio. No avances de etapa sin una instrucción explícita.
