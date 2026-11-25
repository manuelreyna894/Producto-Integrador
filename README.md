# Producto-Integrador
Aplicación web multiusuario para almacenar, organizar y compartir imágenes y documentos, desarrollada con PHP 8 y CodeIgniter 4 bajo el patrón MVC. Cada usuario tiene su propio espacio privado, y un panel de administración permite gestionar cuentas y moderar contenido.


CARACTERISTICAS
-Galeria de imagenes

* Subida múltiple (hasta 20 imágenes por envío) con arrastrar y soltar, vista previa y barra de progreso.
* Validación doble: en el navegador (tipo y tamaño) y de nuevo en el servidor (JPG, PNG, GIF, WEBP; máx. 5 MB).
* Miniaturas automáticas de 400×300 generadas con la librería de imágenes de CodeIgniter (GD).
* Organización por álbumes, orden configurable, edición de título/álbum y búsqueda instantánea sin recargar la página.
* Visor a pantalla completa con navegación por botones y flechas del teclado.

GESTION DE ARCHIVOS

* Subida de documentos (PDF, Office, TXT, CSV, ZIP, imágenes; máx. 10 MB) con lista blanca de extensiones que bloquea archivos peligrosos como .php o .exe.
* Visualización en línea, descarga y control de espacio utilizado por usuario.

GENERACION DE DOCUMENTOS
* Creación de documentos propios y exportación a Word (.docx), Excel (.xlsx), PDF, imagen PNG y JSON desde una misma librería (GeneradorDocumentos), reutilizada también por el reporte de archivos.

USUARIOS Y SEGURIDAD
* Autenticación con sesiones (con regeneración de ID al iniciar sesión) y cookies (“recordar usuario”, último acceso).
* Roles usuario y admin, aplicados mediante filtros de CodeIgniter (Auth, Admin) antes de llegar al controlador.
* Contraseñas con password_hash (bcrypt) y migración automática de hashes antiguos (MD5).
* Aislamiento por usuario: cada consulta filtra por el usuario en sesión; un usuario no puede ver recursos de otro por URL.
* Archivos almacenados en writable/uploads/, fuera de la carpeta pública, y entregados a través de controladores que verifican la propiedad.
* Protección CSRF en formularios, validación de entradas y nombres de archivo aleatorios.

PANEL DE ADMINISTRACION
* Alta, edición y baja de usuarios, estadísticas por usuario, revisión de galerías y eliminación de imágenes.


🧱 Arquitectura

app/
├── Config/Routes.php      # Rutas agrupadas por filtros (público / sesión / admin)
├── Controllers/           # Home, User, Panel, Imagenes, Archivos, Documentos, Publication, Admin
├── Models/                # UserModel, ImagenModel, AlbumModel, ArchivoModel, DocumentoModel, PublicationModel
├── Filters/               # Auth (sesión) y Admin (rol)
├── Libraries/             # GeneradorDocumentos (Word, Excel, PDF, PNG, JSON)
├── Helpers/               # Funciones auxiliares para las vistas
└── Views/                 # Plantillas (header/footer, parciales, módulos)
public/
├── js/galeria.js          # Lógica del lado del cliente
└── css/app.css
database.sql               # Esquema completo + usuario administrador inicial

Base de datos (MySQL): user, publication, archivo, documento, album, imagen.

🛠️ Tecnologías
Capa	Tecnología
Backend	PHP 8, CodeIgniter 4 (MVC, filtros, validación, sesiones)
Base de datos	MySQL / MariaDB
Frontend	HTML5, Bootstrap 4, Bootstrap Icons, JavaScript (ES5, AJAX)
Librerías	PHPWord, PhpSpreadsheet, TCPDF (vía Composer), GD
Despliegue	XAMPP (local) · hosting con Apache (.htaccess incluido)


🚀 Instalación local
Requisitos: PHP 8.1+, Composer, MySQL/MariaDB (por ejemplo XAMPP) y las extensiones intl, mbstring, gd y mysqli.

1.- Clona el repositorio y entra a la carpeta:

git clone https://github.com/<tu-usuario>/<tu-repo>.git
cd <tu-repo>

2.- Instalas las dependencias:
composer install
composer require phpoffice/phpword phpoffice/phpspreadsheet tecnickcom/tcpdf:^6

3.- Crea la base de datos galeria en phpMyAdmin e importa database.sql (pestaña SQL).

4.-Configura el entorno: copia env a .env y ajusta:

CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'
database.default.hostname = localhost
database.default.database = galeria
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi

5.- Levanta el servidor de desarrollo:

php spark serve --port 8080

6.- Abre http://localhost:8080 e inicia sesión con el administrador inicial:
* Usuario: admin
* Contraseña: Admin2026 → cámbiala desde “Mi cuenta” al entrar.

  (writable/ debe tener permisos de escritura (aquí se guardan las imágenes, miniaturas, sesiones y logs).


☁️ Despliegue en hosting

Se incluye un .htaccess que redirige todo a public/ para que app/, writable/ y .env no sean accesibles desde el navegador, y la plantilla env_para_hosting.txt con la configuración de producción (CI_ENVIRONMENT = production).


🗺️ Próximos pasos
* Compartir álbumes mediante enlaces con caducidad.
* Paginación y carga diferida en galerías grandes.
* Pruebas automatizadas con PHPUnit.
* Cuota de almacenamiento configurable por usuario.


📄 Licencia

Proyecto académico. Uso educativo.







