# Aventones-Web1

🛻 AVENTONES – Funcionalidades del Sistema
👥 Tipos de Usuarios

El sistema permite tres tipos de usuarios con diferentes niveles de acceso y funciones:

Administrador

Gestiona los usuarios del sistema.

Crea cuentas de tipo administrador.

Activa o desactiva usuarios (choferes o pasajeros).

Chofer

Registra su perfil personal y su vehículo.

Crea, edita o elimina sus viajes disponibles.

Consulta sus viajes activos y pasados.

Revisa las solicitudes de reserva de los pasajeros.

Puede aceptar o rechazar solicitudes de viaje.

Pasajero

Registra su cuenta personal mediante formulario.

Busca viajes disponibles públicamente.

Solicita reservas en viajes activos.

Consulta sus reservas activas y pasadas.

Puede cancelar reservas activas o pendientes.

🚗 Módulos Principales
🔐 Autenticación y Registro

Formulario de registro con los siguientes datos:

Nombre, apellido, cédula, fecha de nacimiento, correo, teléfono, foto y contraseña.

Se envía un correo de activación con un enlace único para confirmar la cuenta.

Inicio de sesión por correo y contraseña.

Control de sesión activo por tipo de usuario.

👤 Perfil de Usuario

Visualización y edición de la información personal.

Actualización de foto de perfil.

Cambio seguro de contraseña.

Validación de formato de imagen y campos obligatorios.

🚘 Gestión de Viajes (Chofer)

Creación de nuevos viajes indicando:

Origen, destino, fecha, hora, tarifa y asientos disponibles.

Vehículo asociado (marca, modelo, año, placa).

Edición y eliminación de viajes creados.

Vista de viajes activos y completados.

🔎 Búsqueda Pública de Viajes (Pasajero)

Listado público de todos los viajes futuros.

Información visible:

Origen, destino, fecha, hora, vehículo (marca, modelo, año) y asientos disponibles.

Filtros:

Buscar por origen o destino.

Ordenar por fecha, origen o destino (ascendente o descendente).

Solo los pasajeros registrados pueden solicitar una reserva.

📅 Reservas

Pasajeros pueden solicitar reservas en viajes publicados.

Choferes pueden aceptar o rechazar las solicitudes recibidas.

Pasajeros pueden cancelar reservas activas o pendientes.

Visualización de reservas:

Activas: viajes confirmados pendientes de realizar.

Pasadas: viajes ya realizados o cancelados.

🧭 Interfaz General

Interfaz adaptada según el rol del usuario.

Encabezado con logo del sistema.

Menú de navegación dinámico.

Tablas de datos limpias y estilizadas.

Footer uniforme en todas las vistas.

✉️ Notificaciones y Correos

Envío automático de correo de activación al registrarse.

Posibilidad de ampliación para notificaciones futuras (como recordatorios de viaje o estado de reservas).
