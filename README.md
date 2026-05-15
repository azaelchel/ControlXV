# Invitados Zu

Sistema base en Laravel para reemplazar el control de invitados que hoy llevas en Excel.

## Qué trae

- CRUD de invitados
- Grupos, prefijos, categorias, estatus y seguimientos tipo WhatsApp
- Resumen general
- Resumen por grupo
- Resumen por estatus
- SQLite local para trabajar rápido
- Estructura lista para migrar a Railway + PostgreSQL

## Ruta local del proyecto

`/Users/pj/Sites/zu-invitados`

## Correr local

Desde la carpeta del proyecto:

```bash
php artisan serve --host=127.0.0.1 --port=8083
```

Luego abre:

`http://127.0.0.1:8083`

## Base local

Usa:

- `SQLite`
- archivo: `database/database.sqlite`

Si ocupas reiniciar base:

```bash
php artisan migrate:fresh
```

## Campos del sistema

- Grupo
- Prefijo
- Nombre
- Categoria
- Estatus
- Telefono
- Adultos
- Adolescentes
- Niños
- Padrino
- Whats 2 meses
- Whats 1 mes
- Whats 15 dias
- Observaciones

## Deployment en Railway

Según la guía oficial de Railway para Laravel, Railway detecta Laravel automáticamente y lo ejecuta con `php-fpm` y `Caddy`. La guía también recomienda desplegar desde un repositorio de GitHub y agregar PostgreSQL como servicio aparte:

- Guía Laravel: https://docs.railway.com/guides/laravel
- PostgreSQL: https://docs.railway.com/databases/postgresql
- Config as code: https://docs.railway.com/config-as-code/reference

### 1. Preparar repo

Inicializa git si hace falta:

```bash
git init
git add .
git commit -m "Base sistema invitados zu"
```

Después crea un repositorio en GitHub y súbelo:

```bash
git branch -M main
git remote add origin <TU_REPO_GITHUB>
git push -u origin main
```

### 2. Crear proyecto en Railway

1. Nuevo proyecto
2. `Deploy from GitHub repo`
3. Seleccionar este repositorio
4. Agregar un servicio `PostgreSQL`
5. Generar dominio público desde `Settings > Networking > Generate Domain`

### 3. Variables de entorno

En el servicio web, abre `Variables > Raw Editor` y pega el contenido base de `.env.railway.example`.

Genera `APP_KEY` localmente:

```bash
php artisan key:generate --show
```

Luego reemplaza ese valor dentro de las variables de Railway.

### 4. Configuración aplicada en código

Este proyecto ya quedó preparado con:

- `railway.json` usando `RAILPACK`
- `preDeployCommand` para correr migraciones:

```bash
php artisan migrate --force
```

- `healthcheckPath` en `/login`
- restart policy `ON_FAILURE`

### 5. Importante para producción

- Railway no debe usar el `SQLite` local del proyecto.
- La app debe correr con `PostgreSQL`.
- Los logs ya conviene mandarlos a `stderr`.
- El importador desde Excel que apunta a una ruta local de iCloud no servirá igual en Railway hasta que se adapte a carga de archivo desde la web.

## Siguiente paso recomendado

Lo que más conviene hacer después:

1. importar tus datos reales del Excel
2. agregar login
3. exportar a Excel
4. añadir dashboard más fino

## Importar desde tu Excel actual

El sistema ya contempla importar desde:

`/Users/pj/Library/Mobile Documents/com~apple~CloudDocs/Invitados Zu control.xlsx`

Comando:

```bash
php artisan guests:import-excel
```

O desde la interfaz:

- botón `Importar desde Excel`
