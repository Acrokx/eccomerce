# Manual Técnico - Ecommerce Orgánico

## 1. Arquitectura del Sistema

### Tecnologías Utilizadas
- **Backend:** PHP 7.4+ con PDO para base de datos
- **Frontend:** HTML5, CSS3, JavaScript vanilla
- **Base de Datos:** MySQL/MariaDB
- **Servidor Web:** Apache (XAMPP)
- **Librerías:** PHPMailer para notificaciones por email, Composer para gestión de dependencias

### Estructura de Carpetas
```
eccomerse/
├── agregar_producto.php          # Formulario para agregar productos (agricultores)
├── alter_tabla_productos.php     # Script para alterar tabla productos
├── carrito.php                   # Gestión del carrito de compras
├── catalogo.php                  # Catálogo de productos para clientes
├── checkout.php                  # Proceso de checkout y pago
├── composer.json                 # Configuración de Composer
├── composer.lock                 # Lock de dependencias
├── configuracion_basica.php      # Configuración básica del sistema
├── crear_tabla_notificaciones.php # Script para crear tabla notificaciones
├── crear_tabla_productos.php     # Script para crear tabla productos
├── crear_tabla_valoraciones.php  # Script para crear tabla valoraciones
├── crear_tablas_pedidos.php      # Script para crear tablas de pedidos
├── dashboard_agricultor.php      # Dashboard para agricultores
├── dashboard_cliente.php         # Dashboard para clientes
├── formulario_registro.html      # Formulario HTML de registro (obsoleto)
├── login.php                     # Sistema de login
├── logout.php                    # Logout del sistema
├── mis_productos.php             # Gestión de productos del agricultor
├── notificaciones.php            # Funciones de notificación (obsoleto)
├── Notificador.php               # Clase para notificaciones con PHPMailer
├── procesar_registro.php         # Procesamiento de registro (obsoleto)
├── registro.php                  # Registro completo con validaciones
├── valorar_pedido.php            # Sistema de calificación de pedidos
├── vendor/                       # Dependencias de Composer
└── uploads/                      # Carpeta para imágenes de productos
```

### Flujo de Datos
1. **Registro/Login:** Usuario se registra o inicia sesión → Sesión creada
2. **Agricultor:** Agrega productos → Almacenados en BD
3. **Cliente:** Navega catálogo → Agrega a carrito → Checkout → Pedido creado → Notificaciones enviadas
4. **Valoración:** Cliente valora pedido entregado → Promedios actualizados

## 2. Base de Datos

### Modelo Entidad-Relación
```
usuarios (id, nombre, email, password, tipo_usuario, telefono, calificacion_promedio, total_valoraciones, fecha_registro)
    |
    ├── productos (id, agricultor_id, nombre, descripcion, precio, stock, categoria, unidad_medida, certificacion_organica, imagen, fecha_creacion, activo, calificacion_promedio, total_valoraciones)
    │       │
    │       ├── pedido_detalles (id, pedido_id, producto_id, cantidad, precio_unitario, subtotal)
    │       │
    │       └── valoraciones (id, cliente_id, producto_id, agricultor_id, pedido_id, calificacion_producto, calificacion_agricultor, comentario_producto, comentario_agricultor, fecha)
    │
    └── pedidos (id, cliente_id, total, direccion_entrega, metodo_pago, notas, estado, fecha_pedido)
            │
            └── pedido_detalles (ver arriba)

notificaciones (id, usuario_id, tipo, mensaje, enviado, fecha_envio)
```

### Descripción de Tablas

#### usuarios
- **id:** INT AUTO_INCREMENT PRIMARY KEY
- **nombre:** VARCHAR(255) NOT NULL
- **email:** VARCHAR(255) UNIQUE NOT NULL
- **password:** VARCHAR(255) NOT NULL (hash)
- **tipo_usuario:** ENUM('cliente', 'agricultor') NOT NULL
- **telefono:** VARCHAR(20)
- **calificacion_promedio:** DECIMAL(3,2) DEFAULT 0
- **total_valoraciones:** INT DEFAULT 0
- **fecha_registro:** TIMESTAMP DEFAULT CURRENT_TIMESTAMP

#### productos
- **id:** INT AUTO_INCREMENT PRIMARY KEY
- **agricultor_id:** INT NOT NULL (FK → usuarios.id)
- **nombre:** VARCHAR(255) NOT NULL
- **descripcion:** TEXT
- **precio:** DECIMAL(10,2) NOT NULL
- **stock:** INT NOT NULL DEFAULT 0
- **categoria:** VARCHAR(100)
- **unidad_medida:** VARCHAR(50)
- **certificacion_organica:** TINYINT(1) DEFAULT 0
- **imagen:** VARCHAR(255)
- **fecha_creacion:** TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- **activo:** TINYINT(1) DEFAULT 1
- **calificacion_promedio:** DECIMAL(3,2) DEFAULT 0
- **total_valoraciones:** INT DEFAULT 0

#### pedidos
- **id:** INT AUTO_INCREMENT PRIMARY KEY
- **cliente_id:** INT NOT NULL (FK → usuarios.id)
- **total:** DECIMAL(10,2) NOT NULL
- **direccion_entrega:** TEXT NOT NULL
- **metodo_pago:** VARCHAR(50) NOT NULL
- **notas:** TEXT
- **estado:** VARCHAR(50) DEFAULT 'pendiente'
- **fecha_pedido:** TIMESTAMP DEFAULT CURRENT_TIMESTAMP

#### pedido_detalles
- **id:** INT AUTO_INCREMENT PRIMARY KEY
- **pedido_id:** INT NOT NULL (FK → pedidos.id)
- **producto_id:** INT NOT NULL (FK → productos.id)
- **cantidad:** INT NOT NULL
- **precio_unitario:** DECIMAL(10,2) NOT NULL
- **subtotal:** DECIMAL(10,2) NOT NULL

#### valoraciones
- **id:** INT AUTO_INCREMENT PRIMARY KEY
- **cliente_id:** INT NOT NULL (FK → usuarios.id)
- **producto_id:** INT NOT NULL (FK → productos.id)
- **agricultor_id:** INT NOT NULL (FK → usuarios.id)
- **pedido_id:** INT NOT NULL (FK → pedidos.id)
- **calificacion_producto:** INT NOT NULL CHECK (1-5)
- **calificacion_agricultor:** INT NOT NULL CHECK (1-5)
- **comentario_producto:** TEXT
- **comentario_agricultor:** TEXT
- **fecha:** TIMESTAMP DEFAULT CURRENT_TIMESTAMP

#### notificaciones
- **id:** INT AUTO_INCREMENT PRIMARY KEY
- **usuario_id:** INT NOT NULL (FK → usuarios.id)
- **tipo:** VARCHAR(50) NOT NULL
- **mensaje:** TEXT
- **enviado:** TINYINT(1) DEFAULT 0
- **fecha_envio:** TIMESTAMP DEFAULT CURRENT_TIMESTAMP

### Índices para Optimización
```sql
-- Índices en productos
CREATE INDEX idx_productos_agricultor ON productos(agricultor_id);
CREATE INDEX idx_productos_activo ON productos(activo);
CREATE INDEX idx_productos_categoria ON productos(categoria);

-- Índices en pedidos
CREATE INDEX idx_pedidos_cliente ON pedidos(cliente_id);
CREATE INDEX idx_pedidos_estado ON pedidos(estado);

-- Índices en valoraciones
CREATE INDEX idx_valoraciones_producto ON valoraciones(producto_id);
CREATE INDEX idx_valoraciones_agricultor ON valoraciones(agricultor_id);
```

## 3. APIs y Endpoints

Este sistema utiliza páginas PHP directas sin API REST formal. Los "endpoints" son los archivos PHP que procesan formularios POST.

### Páginas Principales
- **GET/POST registro.php:** Registro de usuarios
- **GET/POST login.php:** Inicio de sesión
- **POST logout.php:** Cierre de sesión
- **GET/POST agregar_producto.php:** Agregar productos (agricultores)
- **GET/POST mis_productos.php:** Gestionar productos propios
- **GET catalogo.php:** Ver catálogo de productos
- **POST carrito.php:** Gestionar carrito de compras
- **POST checkout.php:** Procesar pedidos
- **GET/POST valorar_pedido.php:** Valorar pedidos entregados

### Parámetros y Respuestas
Cada página maneja sus propios parámetros POST/GET y muestra respuestas HTML con mensajes de éxito/error.

## 4. Configuración y Despliegue

### Requisitos del Servidor
- **Hardware:** Mínimo 1GB RAM, 10GB disco
- **Software:**
  - PHP 7.4+ con extensiones: pdo, pdo_mysql, mbstring
  - MySQL/MariaDB 5.7+
  - Apache/Nginx con mod_rewrite
  - Composer para dependencias

### Variables de Entorno
Crear archivo `.env` en la raíz:
```
DB_HOST=localhost
DB_NAME=ecommerce_organico
DB_USER=root
DB_PASS=
SMTP_HOST=smtp.gmail.com
SMTP_USER=tu-email@gmail.com
SMTP_PASS=tu-contraseña-app
SMTP_PORT=587
```

### Proceso de Instalación
1. Clonar repositorio en `htdocs/eccomerse/`
2. Instalar dependencias: `composer install`
3. Crear base de datos `ecommerce_organico`
4. Ejecutar scripts de creación de tablas en orden:
   - `crear_tabla_productos.php`
   - `crear_tablas_pedidos.php`
   - `crear_tabla_valoraciones.php`
   - `crear_tabla_notificaciones.php`
5. Configurar SMTP en `Notificador.php`
6. Crear carpeta `uploads/` con permisos 755
7. Acceder a `registro.php` para crear primer usuario

### Actualización de Versiones
1. Backup de base de datos
2. Actualizar código desde repositorio
3. Ejecutar `composer update`
4. Ejecutar scripts de alteración si hay cambios en BD
5. Probar funcionalidades críticas

### Backup de Datos
```bash
# Backup MySQL
mysqldump -u root ecommerce_organico > backup_$(date +%Y%m%d).sql

# Backup archivos
tar -czf backup_files_$(date +%Y%m%d).tar.gz eccomerse/uploads/
```

## 5. Seguridad

### Medidas Implementadas
- **Validación de entrada:** Todos los inputs sanitizados
- **Prepared statements:** Prevención de SQL injection
- **Hash de contraseñas:** password_hash() con PASSWORD_DEFAULT
- **Sesiones seguras:** session_start() con validación de usuario
- **Validación de archivos:** Solo imágenes permitidas en uploads

### Recomendaciones Adicionales
- Implementar HTTPS en producción
- Usar CSP headers
- Monitorear logs de errores
- Actualizar dependencias regularmente

## 6. Mantenimiento

### Logs
- `email_log.txt`: Registro de envíos de email
- Logs de PHP en servidor
- Logs de MySQL

### Monitoreo
- Verificar espacio en disco para uploads
- Monitorear rendimiento de consultas
- Revisar logs de errores diariamente

### Troubleshooting
- **Error de conexión DB:** Verificar credenciales en código
- **Emails no se envían:** Configurar SMTP correctamente
- **Imágenes no se suben:** Verificar permisos de carpeta uploads
- **Sesiones expiran:** Configurar session.gc_maxlifetime en php.ini