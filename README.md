# 🎯 Sistema de Gestión Peña de Fútbol - Temporada 2026

## 📋 Descripción del Proyecto

Este es un sistema web completo para gestionar y mostrar las estadísticas de una peña de fútbol. Permite visualizar clasificaciones ofensivas y de sanciones, además de un panel de administración para gestionar los datos de los jugadores.

### ⚽ Características Principales

- **Clasificación Ofensiva**: Tabla ordenada por puntos (Goles ×3 + Asistencias ×2)
- **Tabla de Sanciones**: Ordenada por puntos de penalización (Amarillas ×(-1) + Rojas ×(-3))
- **Panel de Administración**: Para crear, editar y gestionar estadísticas
- **Diseño Responsivo**: Optimizado para móviles, tablets y desktop
- **Temática Futbolística**: Colores y diseño inspirados en el fútbol
- **API REST**: Backend robusto con PHP y MySQL

## 🗂️ Estructura del Proyecto

```
calicasas/
│
├── index.html              # Interfaz principal
├── config.php              # Configuración de base de datos
├── db.sql                  # Esquema y datos iniciales
│
├── css/
│   └── styles.css          # Estilos responsivos y temáticos
│
├── js/
│   └── app.js              # Lógica del frontend
│
├── api/
│   ├── login.php           # API de autenticación
│   └── players.php         # API de gestión de jugadores
│
└── README.md               # Esta documentación
```

## 👥 Jugadores Incluidos - Temporada 2026

El sistema incluye los siguientes **27 jugadores**:

1. Vicente
2. Pablo
3. Molero
4. Carlos
5. Raul
6. Gonzalez
7. Dani Gonzalez
8. Javier (portero)
9. Torres
10. Horacio
11. Rafa
12. Álvaro
13. Kiko
14. Albertillo
15. Ruben
16. Xaxi
17. Ivan
18. Jose Angel
19. Maikel
20. Andrés
21. Víctor (portero)
22. Purpi
23. Jose Alcala
24. Pepe Polvillo
25. Chini
26. Diego
27. Alexis

**Árbitro**: Juanxus

---

## 🚀 Guía de Instalación en InfinityFree

### Paso 1: Preparar los Archivos

1. **Descargar el proyecto**: Asegúrate de tener todos los archivos del proyecto
2. **Verificar estructura**: Confirma que tienes la estructura de carpetas correcta

### Paso 2: Crear Cuenta en InfinityFree

1. Ve a [infinityfree.com](https://infinityfree.com)
2. Regístrate para una cuenta gratuita
3. Verifica tu email
4. Accede al panel de control

### Paso 3: Crear Subdominio

1. En el panel de InfinityFree, busca **"Subdomain"**
2. Crea un subdominio como: `calicasas.infinityfreeapp.com`
3. Espera a que se active (puede tardar unos minutos)

### Paso 4: Crear Base de Datos MySQL

1. En el panel, busca **"MySQL Databases"**
2. Haz clic en **"Create Database"**
3. Elige un nombre como: `calicasas`
4. **¡IMPORTANTE!** Apunta estos datos:
   - **Hostname**: algo como `sql###.infinityfree.com`
   - **Database Name**: algo como `epiz_12345678_calicasas`
   - **Username**: algo como `epiz_12345678`
   - **Password**: la que hayas elegido

### Paso 5: Configurar Base de Datos

1. Ve a **"phpMyAdmin"** desde el panel
2. Selecciona tu base de datos
3. Ve a la pestaña **"SQL"**
4. Copia y pega todo el contenido del archivo `db.sql`
5. Haz clic en **"Go"** para ejecutar
6. Verifica que se crearon las tablas `users` y `players`

### Paso 6: Configurar config.php

1. Abre el archivo `config.php`
2. Reemplaza los valores de configuración con los tuyos:

```php
// Ejemplo de configuración típica en InfinityFree
define('DB_HOST', 'sql###.infinityfree.com');    // Tu hostname
define('DB_USER', 'epiz_12345678');               // Tu username
define('DB_PASS', 'tu_contraseña_aquí');          // Tu password
define('DB_NAME', 'epiz_12345678_calicasas');     // Tu database name
```

### Paso 7: Subir Archivos por FTP

1. **Opción A - File Manager Web**:
   - Ve a "File Manager" en el panel de InfinityFree
   - Navega a la carpeta `htdocs`
   - Sube todos los archivos manteniendo la estructura

2. **Opción B - Cliente FTP**:
   - Descarga FileZilla o similar
   - Conecta usando los datos FTP de InfinityFree
   - Sube archivos a `/htdocs/`

### Paso 8: Configurar Permisos (si es necesario)

1. Asegúrate de que los archivos PHP tienen permisos de lectura
2. La carpeta `htdocs` debería tener permisos 755

### Paso 9: Probar la Aplicación

1. Ve a tu URL: `https://calicasas.infinityfreeapp.com`
2. Deberías ver las tablas de clasificación
3. Prueba el login con:
   - **Usuario**: `admin`
   - **Contraseña**: `calicacasas`

---

## 🔐 Credenciales por Defecto

### Administrador
- **Usuario**: `admin`
- **Contraseña**: `calicacasas`

**⚠️ IMPORTANTE**: Cambia estas credenciales en producción siguiendo las instrucciones de seguridad.

---

## 📱 Funcionalidades de la Aplicación

### Para Visitantes (Sin Login)
- ✅ Ver clasificación ofensiva ordenada por puntos
- ✅ Ver tabla de sanciones 
- ✅ Actualizar datos en tiempo real
- ✅ Interfaz responsive (móvil, tablet, desktop)

### Para Administradores (Con Login)
- ✅ Crear nuevos jugadores
- ✅ Editar estadísticas de cualquier jugador
- ✅ Reiniciar todas las estadísticas
- ✅ Exportar datos en formato JSON
- ✅ Panel de administración completo

### Sistema de Puntuación
- **Puntos Ofensivos**: Goles ×3 + Asistencias ×2
- **Puntos de Sanción**: Amarillas ×(-1) + Rojas ×(-3)

---

## 🔧 Configuración Avanzada

### Cambiar Contraseña del Administrador

1. **Generar hash seguro**:
```php
<?php
echo password_hash('tu_nueva_contraseña', PASSWORD_DEFAULT);
?>
```

2. **Actualizar en la base de datos**:
```sql
UPDATE users SET password = 'hash_generado_arriba' WHERE username = 'admin';
```

3. **Modificar login.php** para usar `password_verify()`:
```php
// Cambiar esta línea:
if ($password === $db_pass) {

// Por esta:
if (password_verify($password, $db_pass)) {
```

### Habilitar HTTPS

1. En InfinityFree, ve a SSL Certificates
2. Habilita SSL gratuito para tu dominio
3. Fuerza HTTPS añadiendo a `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Personalizar Colores

Edita las variables CSS en `styles.css`:
```css
:root {
    --primary-green: #tu_color_aquí;
    --light-green: #tu_color_aquí;
    --accent-green: #tu_color_aquí;
}
```

---

## 🛠️ API Endpoints

### Públicos
- `GET /api/players.php?action=list` - Obtener lista de jugadores

### Requieren Autenticación
- `POST /api/login.php` - Iniciar sesión
- `POST /api/players.php?action=create` - Crear jugador
- `POST /api/players.php?action=update` - Actualizar estadísticas
- `POST /api/players.php?action=reset` - Reiniciar todas las estadísticas
- `DELETE /api/players.php?action=delete&id=X` - Eliminar jugador

### Ejemplo de Uso de la API

```javascript
// Obtener jugadores
fetch('api/players.php?action=list')
  .then(res => res.json())
  .then(data => console.log(data.players));

// Actualizar jugador (requiere login)
fetch('api/players.php?action=update', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    id: 1,
    goals: 5,
    assists: 3,
    yellows: 1,
    reds: 0
  })
});
```

---

## 🔒 Seguridad Implementada

### Medidas Incluidas
- ✅ Prepared statements para evitar inyección SQL
- ✅ Validación de datos de entrada
- ✅ Sesiones PHP seguras
- ✅ Headers CORS configurados
- ✅ Escape de HTML en outputs
- ✅ Validación de tipos de datos

### Recomendaciones Adicionales
- 🔒 Cambiar contraseña admin y usar `password_hash()`
- 🔒 Habilitar HTTPS en producción
- 🔒 Limitar intentos de login
- 🔒 Implementar captcha para el login
- 🔒 Backups regulares de la base de datos
- 🔒 Monitoreo de logs de errores

---

## 📊 Base de Datos

### Tabla `users`
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
```

### Tabla `players`
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
goals INT DEFAULT 0,
assists INT DEFAULT 0,
yellows INT DEFAULT 0,
reds INT DEFAULT 0,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

---

## 🔧 Solución de Problemas Comunes

### Error "Connection failed"
- ✅ Verifica los datos en `config.php`
- ✅ Confirma que la base de datos está creada
- ✅ Revisa que el hostname sea correcto

### No se muestran los jugadores
- ✅ Verifica que ejecutaste el `db.sql` completo
- ✅ Revisa la consola del navegador para errores JavaScript
- ✅ Confirma que los archivos PHP se suban correctamente

### Error 500 en las APIs
- ✅ Revisa los logs de error de PHP en el panel de InfinityFree
- ✅ Confirma permisos de archivos (755 para carpetas, 644 para archivos)
- ✅ Verifica sintaxis PHP con un validador online

### El diseño se ve roto
- ✅ Confirma que el archivo `css/styles.css` se subió correctamente
- ✅ Verifica que no hay errores de sintaxis CSS
- ✅ Revisa la ruta del CSS en el HTML

---

## 📞 Soporte y Contacto

### Recursos Útiles
- [Documentación de InfinityFree](https://infinityfree.com/support)
- [Foros de InfinityFree](https://forum.infinityfree.com)
- [Guías de PHP y MySQL](https://www.php.net/docs.php)

### Logs y Debugging
- **Logs de Error**: Panel de InfinityFree → Error Logs
- **Debug JavaScript**: F12 → Console en el navegador
- **Debug PHP**: Añadir `error_log()` en el código

---

## 🎉 ¡Listo!

Tu sistema de gestión de la peña de fútbol debería estar funcionando perfectamente. Los usuarios pueden ver las clasificaciones públicamente, y tú puedes gestionar todas las estadísticas desde el panel de administración.

### Próximos Pasos Recomendados
1. 🔒 Cambiar credenciales por defecto
2. 🛡️ Habilitar HTTPS
3. 📱 Probar en diferentes dispositivos
4. 📊 Comenzar a cargar estadísticas reales
5. 🎨 Personalizar colores si es necesario

**¡Que disfrutes gestionando la temporada 2026 de tu peña de fútbol!** ⚽🏆

---

*Desarrollado con ❤️ para la gestión deportiva amateur*