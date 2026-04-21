# Forms Hub PHP

Versión en `PHP 8.0 + MySQL + Bootstrap 5 + JavaScript` del sistema de referencia ubicado en `/Applications/MAMP/htdocs/forms`.

## Requisitos

- PHP 8.0+
- MySQL 5.7+ o 8+
- Apache con `mod_rewrite`

## Configuración rápida

1. Crea la base ejecutando `database/schema.sql`.
2. Carga los datos demo con `database/seed.sql`.
3. Ajusta credenciales en `app/config/config.php`.
4. Abre `http://localhost:8888/form2` en MAMP.

## Credenciales demo

- Usuario: `admin@formshub.local`
- Clave: `Cambiar123!`

## Funcionalidad incluida

- Login admin y cierre de sesión
- Recuperación de contraseña por SMTP configurado en `app/config/config.php`
- CRUD de campañas
- Constructor dinámico de bloques y preguntas
- Vista pública por token
- Registro de respuestas y archivos adjuntos
- Resultados por campaña
- Exportación Excel compatible `.xls`
- QR en la vista pública generado en navegador

## Estructura

- `index.php`: front controller
- `app/Controllers`: flujos HTTP
- `app/Models`: acceso a datos y sincronización de campañas
- `app/views`: vistas PHP
- `public/assets`: CSS y JS
- `public/uploads`: archivos subidos
- `database`: esquema y seed
