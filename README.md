# SelfTest

Plataforma de cuestionarios interactivos para la preparación de exámenes de certificación.

## Características

- Sistema de autenticación y autorización con roles (Admin, Profesor, Usuario)
- Cuestionarios con preguntas de opción múltiple
- Importación/exportación de preguntas desde archivos JSON
- Panel de administración con EasyAdmin
- Filtrado de cuestionarios por categoría, nivel y estado
- Visualización de resultados con puntuación porcentual
- Reset automático semanal de quizzes (configurable)
- Interfaz responsive con Bootstrap 5

## Requisitos

- PHP 8.2+
- Symfony 7.4
- MySQL/MariaDB
- Composer

## Instalación

```bash
# Instalar dependencias
composer install

# Configurar variables de entorno
cp .env .env.local
# Editar .env.local con la configuración de base de datos

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate

# (Opcional) Importar preguntas de ejemplo
php bin/console app:import-json var/Quiz/AZ-900/
```

## Configuración

### Sesión

La sesión está configurada para durar 8 horas (28800 segundos). Los archivos de sesión se almacenan en `var/sessions/`.

### Reset automático de quizzes

Para habilitar el reset automático semanal de los resultados de los quizzes:

1. Crear archivo `.env.local` si no existe
2. Añadir la variable `RESETFILE` con la ruta al archivo que contendrá la fecha del próximo reset:

```bash
RESETFILE="./var/reset_quiz.txt"
```

El sistema:
- Si el archivo no existe, lo crea con fecha +1 semana (sin hacer reset)
- Cuando la fecha actual supera la fecha del archivo, borra todos los tests de usuarios sin rol de profesor/admin
- Actualiza el archivo con la fecha +1 semana

### Roles

- **Admin** - Acceso total al sistema
- **Profesor** - Acceso al panel de administración y gestión de contenido
- **Usuario** - Puede realizar tests y ver sus propios resultados

### Importar/Exportar preguntas

Los archivos de preguntas están en formato JSON en el directorio `var/Quiz/`.

```bash
# Importar un archivo JSON
php bin/console app:import-json var/Quiz/AZ-900/AZ-900_Introduccion.json

# Importar todos los archivos de un directorio
php bin/console app:import-json var/Quiz/AZ-900/

# Con reemplazo de preguntas existentes
php bin/console app:import-json var/Quiz/AZ-900/ --replace
```

#### Formato JSON

```json
{
    "category": "AZ-900",
    "name": "Introduccion",
    "quizzes": [
        {
            "quiz": "Conceptos Básicos",
            "level": 1,
            "questions": [
                {
                    "text": "¿Qué es Azure?",
                    "intro": "<p>Cloud de Microsoft</p>",
                    "answers": [
                        { "text": "Servicio cloud", "correct": true },
                        { "text": "Sistema operativo", "correct": false }
                    ]
                }
            ]
        }
    ]
}
```

> **Campo `intro`**: Es opcional. Permite incluir HTML antes de la pregunta para ilustrar el contexto de forma gráfica (tablas, código, alertas, etc.). El contenido se sanitiza antes de mostrarse, permitiendo usar clases de Bootstrap 5 (por ejemplo: `<table class="table table-striped">`, `<pre>`, `<div class="alert">`, etc.).

#### Exportar desde la aplicación

En la página principal, al seleccionar un topic específico, aparece el botón "Exportar" que descarga el archivo JSON con todas las preguntas de ese topic.

### Comandos adicionales

```bash
# Crear o actualizar usuario administrador
php bin/console app:create-admin                    # usuario: admin, contraseña aleatoria
php bin/console app:create-admin admin               # usuario: admin, contraseña aleatoria
php bin/console app:create-admin admin MiPass123    # usuario: admin con contraseña específica
php bin/console app:create-admin juan MiPass456 "Juan Pérez"  # usuario personalizado
```