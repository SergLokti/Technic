<?php
/**
 * =====================================================
 * GLPI PLUGIN: TECHNIC v1.0 
 * =====================================================
 * 
 * НАЗНАЧЕНИЕ:
 * Автоматизация задач по управлению доступами:
 * - LDAP (добавление/удаление из групп AD)
 * - SCCM (установка ПО через коллекции)
 * - PowerShell (архивация почты)
 * - Согласования (c владельцами активов)
 * 
 * СОВМЕСТИМОСТЬ: ТОЛЬКО GLPI 10.x
 * 
 * АВТОР: Sergey Loktionov
 * ДАТА: 01.02.2026
 * =====================================================
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * ПРОВЕРКА СОВМЕСТИМОСТИ
 * -----------------------
 * Плагин работает только с GLPI 10.0.x
 * 
 * @return bool true если версия подходит
 */
function plugin_technic_check_prerequisites() {
   
   // Минимальная версия: GLPI 10.0.0
   if (version_compare(GLPI_VERSION, '10.0.0', '<')) {
      echo "❌ Требуется GLPI 10.0.0 или выше<br>";
      echo "Текущая версия: " . GLPI_VERSION;
      return false;
   }
   
   // Максимальная версия: GLPI 10.0.99
   // В GLPI 11+ плагины Fields и GenericObject включены в ядро
   if (version_compare(GLPI_VERSION, '11.0.0', '>=')) {
      echo "❌ Этот плагин несовместим с GLPI 11+<br>";
      echo "Причина: В GLPI 11 плагины Fields и GenericObject в ядре<br>";
      echo "Текущая версия: " . GLPI_VERSION;
      return false;
   }
   
   return true;
}

/**
 * ПРОВЕРКА ЗАВИСИМОСТЕЙ
 * ----------------------
 * Проверяет наличие плагинов Fields и GenericObject
 * 
 * @return bool true если все плагины активны
 */
function plugin_technic_check_config() {
   
   // PLUGIN: FIELDS
   // Необходим для дополнительных полей активов (LDAP группы)
   if (!Plugin::isPluginActive('fields')) {
      echo "⚠️ Требуется плагин 'Fields'<br>";
      echo "Путь: Setup → Plugins → Fields → Install → Activate";
      return false;
   }
   
   // PLUGIN: GENERICOBJECT
   // Необходим для пользовательских объектов (Mail, Share и т.д.)
   if (!Plugin::isPluginActive('genericobject')) {
      echo "⚠️ Требуется плагин 'Generic Object'<br>";
      echo "Путь: Setup → Plugins → Generic Object → Install → Activate";
      return false;
   }
   
   return true;
}

/**
 * ИНФОРМАЦИЯ О ПЛАГИНЕ
 * ---------------------
 * Отображается в Setup → Plugins
 * 
 * @return array Метаданные плагина
 */
function plugin_version_technic() {
   return [
      'name'           => 'Technic',
      'version'        => '1.0.0',
      'author'         => 'Sergey Loktionov',
      'license'        => 'GPLv2+',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => '10.0.0',   // Минимум GLPI 10.0.0
            'max' => '10.0.99'   // Максимум GLPI 10.0.x
         ],
      ]
   ];
}

/**
 * ИНИЦИАЛИЗАЦИЯ ПЛАГИНА
 * ----------------------
 * Вызывается при каждой загрузке GLPI (если плагин активен)
 * Регистрирует хуки и обработчики
 */
function plugin_init_technic() {
   global $PLUGIN_HOOKS;
   
   // 1. CRON TASK
   // Автоматическая обработка задач по расписанию
   // Настройка: Setup → Automatic actions → plugin_technic_cron
   $PLUGIN_HOOKS['cron']['technic'] = 1;
   
   // 2. CSRF ЗАЩИТА
   // Включаем защиту от CSRF атак
   $PLUGIN_HOOKS['csrf_compliant']['technic'] = true;
   
   // 3. РЕГИСТРАЦИЯ КЛАССОВ
   // Автозагрузка классов плагина
   Plugin::registerClass('PluginTechnicCron');
}

?>
