# Subir Fresh Cuttz a Vercel

El sitio corre en Vercel como **contenedor** (`Dockerfile` + `Caddyfile`, FrankenPHP con PHP 8.4).
El runtime comunitario `vercel-php` se archivó en oct-2026 y Vercel recomienda este camino.
En XAMPP todo sigue funcionando igual: sin variables de entorno se usa `root@127.0.0.1/freshcuttz`.

## Qué cambió para que funcione en Vercel

| Problema en Vercel | Solución |
| --- | --- |
| No hay MySQL incluido | `includes/conexion.php` lee `DB_*` del entorno (MySQL externo con SSL) |
| El disco de cada instancia es temporal y no se comparte | Sesiones en la tabla `sesiones` (`includes/sesion.php`) |
| Las fotos subidas desde el panel se perderían | Se guardan en la tabla `imagenes` y las sirve `imagen.php` con la misma URL |
| `.htaccess` no existe fuera de Apache | El `Caddyfile` bloquea `includes/`, `db/` y archivos ocultos |
| MySQL 8 no acepta `PERSISTENT` (MariaDB) | `db/freshcuttz.sql` usa `STORED` |

## Pasos

1. **Crear la base MySQL en la nube** (MySQL 8 con SSL; por ejemplo Aiven MySQL o TiDB Cloud) y anotar host, puerto, usuario y contraseña.
2. **Cargar el esquema** desde tu Mac:
   ```bash
   /Applications/XAMPP/xamppfiles/bin/mysql -h HOST -P PUERTO -u USUARIO -p --ssl < db/freshcuttz.sql
   ```
3. **Crear el administrador** contra la base remota:
   ```bash
   DB_HOST=… DB_PORT=… DB_USER=… DB_PASSWORD=… DB_SSL=1 \
     /Applications/XAMPP/xamppfiles/bin/php db/crear_admin.php usuario 'contraseña-segura'
   ```
4. **Variables en Vercel** (Settings → Environment Variables, ver `.env.example`):
   `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_SSL=1`.
5. **Desplegar** desde la carpeta del proyecto:
   ```bash
   vercel link      # crea o elige el proyecto; framework: Container
   vercel deploy    # vista previa
   vercel deploy --prod
   ```
6. **Fotos de productos que subiste en XAMPP** (`image/productos/producto-*`): si existen en tu disco viajan dentro de la imagen; las nuevas se guardan en MySQL.

## Etiqueta NFC

Graba en la etiqueta la URL de producción: `https://TU-DOMINIO/visita.php?t=TOKEN`
(el token está en `configuracion.nfc_token`). Si cambias el token, las etiquetas viejas dejan de sumar sellos.
