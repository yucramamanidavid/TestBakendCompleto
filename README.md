# 📦 ReservasBack – Backend para Gestión de Turismo

Este proyecto es una API REST desarrollada en **Laravel 10** que permite la gestión de lugares turísticos y tours en el Perú. Soporta operaciones CRUD, validación, subida de imágenes y pruebas automatizadas. Está lista para ser desplegada con **Docker**, **Jenkins** y analizada con **SonarQube**.

---

## 🚀 Características

- CRUD completo para **Tours** y **Places**
- Soporte de imagen vía `form-data`
- Pruebas **unitarias** y **funcionales** con PHPUnit
- Configuración de CI/CD con Jenkins
- Integración con SonarQube para análisis de calidad de código
- Cobertura de código generada en formato XML y texto

---

## 🧰 Requisitos

- PHP >= 8.2
- Composer
- MySQL 8+
- Docker & Docker Compose
- Jenkins (opcional)
- Node.js + Angular (para frontend, opcional)

---

## ⚙️ Instalación Local

```bash
git clone https://github.com/ROSAURA12345/TestWebcapa.git
cd TestWebcapa/reservasback
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
---
Asegúrate de configurar .env con tus credenciales de base de datos correctamente.
---
## 🐳 Entorno Dockerizado
```bash
docker-compose up -d --build

  -Esto levanta:
  -Jenkins (puerto 9080)
  -MySQL (puerto 3307)
  -SonarQube (puerto 9000)
---
## 🧪 Pruebas Automatizadas
```bash
Copiar
Editar
php artisan test
# o bien:
./vendor/bin/phpunit
---
## Pruebas ubicadas en:
```bash
swift
Copiar
Editar
tests/Unit/           → pruebas unitarias
tests/Feature/        → pruebas integrales
---
## Cobertura generada en:
```bash
pgsql
Copiar
Editar
storage/coverage.xml
storage/coverage.txt
---
## 📬 Pruebas Postman
Se incluye una colección de pruebas para Place con endpoints:

GET /api/places

POST /api/places

PUT /api/places/{id}

DELETE /api/places/{id}

Puedes importar el archivo JSON en Postman o crear manualmente según las rutas.
---
##🔄 CI/CD Pipeline (Jenkins)
Archivo Jenkinsfile incluido para:

Clonar repo

Instalar dependencias

Ejecutar migraciones

Correr pruebas y generar cobertura

Analizar con SonarQube

Verificar Quality Gate
---
##🔍 Análisis SonarQube
Incluye configuración:

Análisis de código

Métricas de calidad

Detección de bugs y duplicación

Requiere tener el token Sonarqube en Jenkins > Credentials.
---
##🌍 Endpoints API
Base URL: http://localhost:8000/api

Método	Ruta	Descripción
GET	/places	Lista todos los lugares
POST	/places	Crea un nuevo lugar
PUT	/places/{id}	Actualiza un lugar
DELETE	/places/{id}	Elimina un lugar
GET	/tours	Lista todos los tours
POST	/tours	Crea un nuevo tour
...	...	

👩‍💻 Autor
Desarrollado por: David Robert Yucra mamani
Repositorio: https://github.com/ROSAURA12345/TestWebcapa
---
