# Drupal Weather

Este proyecto es una aplicación Drupal 11 que muestra información meteorológica conectando con una API externa usando un módulo personalizado y un tema propio.

## Características

- Módulo personalizado `weather_api` con conexión a API externa y uso de hooks.
- Tema personalizado `weather` con twigs personalizados modernos.
- Página principal con formulario de búsqueda de ciudad y resultados meteorológicos.

## Requisitos Previos

- [Docker](https://www.docker.com/)
- [DDEV](https://ddev.readthedocs.io/en/stable/)

## Instalación rápida

1. **Activa Docker**

   Asegúrate de que Docker esté instalado y ejecutándose.

2. **Ejecuta el script de inicialización**

   En la raíz del proyecto, ejecuta:

   ```bash
   bash script.sh
   ```

   Esto configurará DDEV, instalará dependencias y Drupal, y activará el módulo y tema personalizados.

3. **Accede al sitio**

   Abre tu navegador y visita:

   ```
   http://drupal-weather.ddev.site
   ```

## Notas

- Usuario administrador: `admin`
- Contraseña: