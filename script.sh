#!/bin/bash

# Detener ejecución si ocurre un error
set -e

# Paso 1: Configurar DDEV
echo "Configurando DDEV para Drupal 11..."
ddev config --project-type=drupal11 --create-docroot --docroot=web

# Paso 2: Instalar dependencias con Composer
echo "Instalando dependencias con Composer..."
ddev composer install

# Paso 3: Instalar Drupal con perfil standard
echo "Instalando Drupal..."
ddev drush si standard -y \
  --account-name=admin \
  --account-pass=admin \
  --site-name="Weather App"

# Paso 4: Activar módulos necesarios
echo "Activando módulos necesarios..."
ddev drush en weather_api -y

# Paso 5: Activar y asignar el tema personalizado
echo "Activando tema 'weather'..."
ddev drush theme:enable weather -y
ddev drush config:set system.theme default weather -y

# Paso 6: Eliminar bloques
ddev drush entity:delete block weather_page_title
ddev drush entity:delete block weather_primary_admin_actions
ddev drush entity:delete block weather_search_form_wide
ddev drush entity:delete block weather_primary_local_tasks
ddev drush entity:delete block weather_search_form_narrow
ddev drush entity:delete block weather_account_menu
ddev drush entity:delete block weather_secondary_local_tasks
ddev drush entity:delete block weather_breadcrumbs
ddev drush entity:delete block weather_help
ddev drush entity:delete block weather_powered
ddev drush entity:delete block weather_messages

# Cambia el orden de los bloques en la región header
ddev drush config:set block.weather_site_branding weight 0 -y
ddev drush config:set block.weather_main_menu weight 1 -y

# Paso 7: Establecer la página de inicio
ddev drush config:set system.site page.front "/weather" -y

echo "Proyecto listo: http://drupal-weather.ddev.site"