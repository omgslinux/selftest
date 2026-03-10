# SelfTest

Plataforma de cuestionarios interactivos para la preparación de exámenes de certificación.

## Características

- Sistema de autenticación y autorización con roles (Admin, Profesor, Usuario)
- Cuestionarios con preguntas de opción múltiple
- Importación de preguntas desde archivos CSV
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
php bin/console app:import-questions var/Quiz/AZ-900/
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

### Importar preguntas

```bash
# Un archivo CSV
php bin/console app:import-questions archivo.csv

# Con reemplazo de preguntas existentes
php bin/console app:import-questions archivo.csv --replace

# Directorio completo
php bin/console app:import-questions var/Quiz/AZ-900/
```

#### Formato CSV

```csv
question;answer;correct
¿Qué es Azure?;Servicio de nube de Microsoft;true
¿Qué es Azure?;Un sistema operativo de escritorio;false
```

### Comandos adicionales

```bash
# Crear o actualizar usuario administrador
php bin/console app:create-admin                    # usuario: admin, contraseña aleatoria
php bin/console app:create-admin admin               # usuario: admin, contraseña aleatoria
php bin/console app:create-admin admin MiPass123    # usuario: admin con contraseña específica
php bin/console app:create-admin juan MiPass456 "Juan Pérez"  # usuario personalizado
```
