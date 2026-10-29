# Ciuro Transporte

Panel administrativo privado para gestionar viajes, hojas de ruta, mercancías, rendiciones, cobros, gastos y cheques. Está construido con Laravel 12, PHP 8.2+ y Filament 4. El `.env.example` usa SQLite para iniciar rápidamente; también se incluyen conexiones para PostgreSQL y MySQL.

## Requisitos

- PHP 8.2 o superior y extensiones habituales de Laravel: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `PDO`, `session`, `tokenizer`, `xml` y el driver PDO de la base de datos seleccionada.
- Composer 2.
- Cloudflared solo para publicar el servidor local mediante un túnel.

## Instalación local (SQLite)

Desde la carpeta del proyecto:

```powershell
Copy-Item .env.example .env
New-Item -ItemType File -Force database/database.sqlite
composer install
php artisan key:generate
php artisan filament:assets
```

Edita `.env` y define `ADMIN_PASSWORD` con una contraseña propia antes de sembrar el usuario. Luego:

```powershell
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Abre <http://localhost:8000/admin> e inicia sesión con el valor de `ADMIN_EMAIL` (por defecto `admin@ciuro.com`) y `ADMIN_PASSWORD`. Si `ADMIN_PASSWORD` se deja vacío en ambiente local, el seeder crea una contraseña aleatoria y la imprime una sola vez en la consola. En producción el seeder falla si no se configura una contraseña.

El seeder es idempotente: actualizará el usuario administrador de esa dirección. No crea choferes ni otros usuarios.

`composer.lock` conserva las versiones resueltas del proyecto; `composer install` las instala y publica automáticamente los assets de Filament. `php artisan filament:assets` también se puede volver a ejecutar después de una actualización o si el panel aparece sin estilos. Luego haz una recarga forzada del navegador (`Ctrl+F5`). No hace falta ejecutar `composer update` en cada inicio.

## Uso con XAMPP

Para SQLite no necesitas iniciar MySQL en XAMPP. `php artisan serve` levanta el servidor de desarrollo de Laravel en el puerto 8000; Apache de XAMPP tampoco interviene. Si prefieres MySQL, crea una base vacía desde phpMyAdmin y cambia `.env` a `DB_CONNECTION=mysql`, con los datos del servidor XAMPP (`DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD`). Después ejecuta las migraciones.

## PostgreSQL o MySQL

Configura `DB_CONNECTION` como `pgsql` o `mysql`, y completa `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`. Crea previamente la base de datos y asegúrate de tener habilitado el driver PDO correspondiente; ejecuta:

```powershell
php artisan migrate --seed
```

## Cloudflare Tunnel

Para una demostración rápida, con el servidor local ya ejecutándose, abre otra terminal y ejecuta:

```powershell
cloudflared tunnel --url http://localhost:8000
```

Cloudflare mostrará una URL temporal `trycloudflare.com`. Para una dirección estable, configura un túnel nombrado y un hostname en tu cuenta de Cloudflare. En un despliegue persistente configura `APP_URL` con el hostname HTTPS, usa `APP_ENV=production`, `APP_DEBUG=false`, credenciales seguras y una contraseña de administrador fuerte; conserva el proceso del servidor de aplicación detrás del túnel. El comando de túnel rápido es temporal y está pensado para pruebas.

## Estructura funcional

- `/admin`: panel con tabla de viajes, creación en modal y edición.
- Detalle de cada viaje: botón «Hoja de ruta ↹ / Rendición ↹» con edición de registros de ruta, mercancías, cobros, gastos y cheques.
- Los códigos de viaje se generan como 3 letras mayúsculas y 6 dígitos. Los importes de mercancías se calculan al guardar, redondeados a dos decimales, y el IVA aplicado es 21%.
- Los estados de mercancía disponibles son: Pagó con efectivo, Pagó con transferencia, No pagó, Pagó con cheque y Pagó con tarjeta.

## Notas de datos

Las columnas `costo_sin_iva` y `costo_con_iva` se persisten y se recalculan en el modelo al guardar cada mercancía. Los totales de hoja y rendición son atributos calculados de Eloquent. Los montos son valores decimales y no se almacenan como punto flotante en la base de datos.
