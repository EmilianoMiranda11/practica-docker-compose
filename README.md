# Sistema de Control de Incidencias - Docker Compose

Práctica individual desarrollada para la materia de Infraestructura de Servicios, implementando una arquitectura multicontenedor orquestada con Docker Compose.

---

## Descripción y Arquitectura de Servicios
El sistema consta de cinco servicios interconectados a través de una red interna personalizada (`app_network`):

1. **`db` (MariaDB):** Base de datos relacional para la persistencia de las incidencias, respaldada por un volumen de Docker (`db_data`) para conservar la información ante reinicios.
2. **`redis` (Redis Alpine):** Capa de almacenamiento en memoria utilizada para el sistema de caché de consultas y un contador de accesos.
3. **`mailpit` (Mailpit):** Servidor SMTP de pruebas diseñado para interceptar y visualizar en tiempo real las notificaciones de correo electrónico enviadas por el backend.
4. **`backend` (PHP 8.3 / Apache):** API RESTful que procesa la lógica de la aplicación, gestiona la conexión con MariaDB, interactúa con Redis y envía correos mediante sockets TCP.
5. **`frontend` (Nginx Alpine):** Interfaz web para el usuario y proxy inverso configurado para redirigir las peticiones de la API (`/api/`) hacia el backend.

---

## Requisitos Previos
* [Docker](https://www.docker.com/) instalado.
* [Docker Compose](https://docs.docker.com/compose/) (versión v2 o superior).

---

## Comandos de Ejecución
1. Configurar las variables de entorno a partir de la plantilla:
   ```bash
   cp .env.example .env
2. Construir y poner en marcha los contenedores en segundo plano:
   ```bash
   docker compose up -d --build
3. Verificar que los cinco servicios se encuentren activos y saludables:
   ```bash
   docker compose ps

## URLs de Acceso Local
* Interfaz Web (Frontend - Control de Incidencias): http://localhost:8081
* Bandeja de Correo (Mailpit): http://localhost:8025
