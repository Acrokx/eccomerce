# Manual de Usuario - Ecommerce Orgánico

## Introducción

Bienvenido a Ecommerce Orgánico, la plataforma que conecta agricultores orgánicos con consumidores conscientes de la alimentación saludable. Esta plataforma permite a los agricultores vender sus productos directamente y a los clientes comprar alimentos frescos y certificados.

## Para Clientes (Compradores)

### 1. Registro y Perfil

#### Cómo crear una cuenta
1. Accede a `http://localhost/eccomerse/registro.php`
2. Completa el formulario con:
   - Nombre completo
   - Correo electrónico válido
   - Contraseña (mínimo 6 caracteres)
   - Tipo de usuario: selecciona "Cliente"
3. Haz clic en "Registrarse"
4. Recibirás confirmación de registro exitoso

#### Gestionar tu perfil
- Actualmente, la edición de perfil no está implementada
- Tu información se guarda de forma segura

### 2. Búsqueda de Productos

#### Navegar el catálogo
1. Inicia sesión en `login.php`
2. Ve al Dashboard y haz clic en "Ver Catálogo de Productos"
3. Explora los productos disponibles

#### Usar filtros de búsqueda
- **Búsqueda por texto:** Escribe nombre o descripción del producto
- **Categoría:** Selecciona una categoría específica
- **Precio máximo:** Establece límite de precio
- **Solo certificados:** Marca para ver solo productos orgánicos certificados
- Haz clic en "Buscar" para aplicar filtros
- Usa "Limpiar" para resetear filtros

### 3. Carrito de Compras

#### Agregar productos
1. En el catálogo, selecciona cantidad deseada
2. Haz clic en "Agregar al Carrito"
3. El contador del carrito se actualiza

#### Gestionar carrito
1. Haz clic en "Carrito" en la parte superior
2. Verás todos los productos agregados
3. Para modificar cantidad:
   - Usa botones + y - junto a cada producto
   - O ingresa cantidad manualmente y actualiza
4. Para remover producto: clic en "Remover"
5. Para vaciar carrito: clic en "Vaciar Carrito"

### 4. Proceso de Pago

#### Realizar compra
1. En el carrito, haz clic en "Proceder al Pago"
2. Completa la información de entrega:
   - Dirección completa
   - Método de pago (efectivo, transferencia, tarjeta)
   - Notas adicionales (opcional)
3. Revisa el resumen del pedido
4. Haz clic en "Confirmar Pedido"

#### Métodos de pago disponibles
- **Efectivo contra entrega:** Paga al recibir
- **Transferencia bancaria:** Coordina con el agricultor
- **Tarjeta de crédito/débito:** Próximamente

### 5. Seguimiento de Pedidos

#### Ver estado de pedidos
- Actualmente no implementado
- Próximamente podrás ver historial de pedidos

#### Contactar agricultores
- Información de contacto disponible en productos
- Teléfono del agricultor mostrado en detalles

### 6. Valoraciones

#### Calificar productos y agricultores
1. Una vez entregado el pedido, recibirás enlace para valorar
2. Accede a `valorar_pedido.php?pedido_id=X`
3. Para cada producto:
   - Selecciona estrellas (1-5) para el producto
   - Escribe comentario opcional
4. Para el agricultor:
   - Selecciona estrellas (1-5)
   - Escribe comentario opcional
5. Haz clic en "Enviar Valoraciones"

## Para Agricultores (Vendedores)

### 1. Registro como Agricultor

#### Crear cuenta de agricultor
1. Accede a `registro.php`
2. Completa formulario seleccionando "Agricultor" como tipo
3. Actualmente no hay verificación de documentos
4. Tu cuenta estará lista para vender

### 2. Gestión de Productos

#### Agregar productos
1. Inicia sesión como agricultor
2. En Dashboard, haz clic en "Agregar Nuevo Producto"
3. Completa el formulario:
   - Nombre del producto
   - Descripción detallada
   - Precio en COP
   - Stock disponible
   - Categoría
   - Unidad de medida (kg, litros, unidades)
   - Marca si tiene certificación orgánica
   - Sube imagen del producto (opcional)
4. Haz clic en "Agregar Producto"

#### Gestionar productos existentes
1. En Dashboard, haz clic en "Ver Mis Productos"
2. Verás tabla con todos tus productos
3. Para editar: clic en "Editar" (próximamente)
4. Para eliminar: clic en "Eliminar" (marca como inactivo)

### 3. Administración de Pedidos

#### Ver pedidos
- Actualmente no implementado
- Próximamente podrás ver pedidos recibidos

#### Actualizar estados
- Próximamente podrás cambiar estado de pedidos

### 4. Dashboard de Ventas

#### Ver estadísticas
- Actualmente muestra información básica
- Próximamente incluirá métricas de ventas

### 5. Comunicación con Clientes

#### Responder consultas
- Información de contacto del cliente en pedidos
- Teléfono proporcionado en registro

### 6. Certificaciones

#### Mantener certificaciones
- Actualmente manual en formulario de producto
- Marca productos con certificación orgánica

## Solución de Problemas

### Problemas Comunes

#### No puedo iniciar sesión
- Verifica email y contraseña
- Asegúrate de estar registrado

#### Producto no aparece en catálogo
- Verifica que esté activo y tenga stock > 0
- Agricultor debe haberlo agregado correctamente

#### Error al subir imagen
- Verifica que sea formato JPG, PNG o GIF
- Tamaño máximo recomendado: 2MB

#### Email de confirmación no llega
- Revisa carpeta spam
- Configuración SMTP debe estar correcta

### Contacto de Soporte

Para problemas técnicos:
- Revisa logs en `email_log.txt`
- Verifica configuración de base de datos
- Asegúrate de que XAMPP esté ejecutándose

## Glosario

- **Producto orgánico:** Alimento cultivado sin químicos artificiales
- **Certificación orgánica:** Sello oficial que garantiza prácticas orgánicas
- **Stock:** Cantidad disponible de producto
- **Carrito:** Lista temporal de productos para comprar
- **Checkout:** Proceso final de compra

## Actualizaciones

Este manual se actualizará con nuevas funcionalidades:
- Sistema de pedidos para agricultores
- Dashboard avanzado con estadísticas
- Sistema de mensajería
- Integración con pasarelas de pago
- App móvil complementaria