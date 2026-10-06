# Gestor de Tareas Pro

Un sistema web seguro y moderno desarrollado en PHP y MySQL para la gestión de tareas por usuario. Proyecto desarrollado para el Desafío de Cloud Computing.

## Características
- **Autenticación Segura:** Sistema completo de Iniciar Sesión y Registro de usuarios.
- **Seguridad de Datos:** Prevención de Inyecciones SQL usando sentencias preparadas y encriptación de contraseñas con `password_hash`.
- **CRUD Completo:** Interfaz intuitiva para Crear, Leer, Editar y Eliminar tareas.
- **Gestión de Estados:** Permite marcar las tareas como "Completadas" o mantenerlas "Pendientes".
- **Totalmente Responsivo y en Español:** Interfaz amigable adaptable a cualquier pantalla.

## Requisitos
- Servidor web (Apache recomendado).
- PHP 8.0 o superior.
- Base de datos MySQL o MariaDB.

## Instalación y Despliegue
1. Clona el repositorio en el directorio raíz de tu servidor web (ej. `C:\xampp\htdocs\` en local o `/var/www/html/` en Linux):
   ```bash
   git clone https://github.com/Sebitafx/desafio-cloud-php.git
   ```
2. Crea una base de datos en MySQL llamada `phplogin`.
3. Importa el archivo `phplogin.sql` incluido en la raíz de este proyecto para estructurar las tablas (`accounts` y `tasks`).
4. (Opcional) Si tu servidor usa una contraseña para la base de datos, actualiza las variables `$DATABASE_USER` y `$DATABASE_PASS` en la cabecera de los archivos principales.
5. Accede a la aplicación desde tu navegador.

## Estructura del Proyecto
- `index.php`: Página principal y formulario de inicio de sesión.
- `register.php`: Formulario de creación de cuenta.
- `home.php`: Panel principal de bienvenida (Dashboard).
- `tasks.php`: Gestor de tareas del usuario (Operaciones CRUD).
- `profile.php`: Visualización de detalles de la cuenta.
- `authenticate.php` / `register-process.php`: Procesamiento backend de credenciales.
- `style.css`: Hoja de estilos del diseño del sistema.
