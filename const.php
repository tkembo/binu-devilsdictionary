<?php
define('APP_ID', '100');
define('DEV_ID', '100');

define('IMG_PATH', 'images/');
define('DEVICE_ID', isset($_COOKIE['binusys_device_id']) ? $_COOKIE['binusys_device_id'] : '0000' );
define('DEVICE_IP', isset($_COOKIE['binusys_ip_addr']) ? $_COOKIE['binusys_ip_addr'] : ( isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0') );
define('USER_AGENT', isset($_COOKIE['binusys_user_agent']) ? $_COOKIE['binusys_user_agent'] : ( isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'No UA') );

define('STATIC_TTL', '3600');
define('START_PAGE_TTL', '3600');
define('READ_TTL', '3600');

define('DEFAULT_TRANSLATION_ID', '5');
define('DEFAULT_ARABIC_TRANSLATION_ID', '6');
define('DEFAULT_TAB', 0);
define('DEFAULT_INTERFACE_LANGUAGE', 'en');
define('RTL_LANGUAGES', 'fa,ar,ha,ur,dv');

// prefix used for the quran translation tables
define('QURAN_TRANS_PREFIX', 'quran_translation_');
define('DEAFAULT_ARABIC_TRANS_TABLE', QURAN_TRANS_PREFIX . DEFAULT_ARABIC_TRANSLATION_ID);
define('AYA_PER_PAGE', 5);

define('FOOTER_BG', '888800');

define('MY_BINU_URL', 'http://pip/home/');
define('SPIDER_DEFAULT', 'Y');

define('MYSQL_HOST', 'localhost');
define('MYSQL_USER', 'user');
define('MYSQL_PASSWORD', 'pass');
define('MYSQL_DB', 'quran_db');
define('MYSQL_BINARY', '/usr/bin/mysql');

if ( isset($_COOKIE['binusys_size']) ) {
  $screen_dimensions = explode('x', $_COOKIE['binusys_size']);
  define('SCREEN_WIDTH', $screen_dimensions[0]); 
  define('SCREEN_HEIGHT', $screen_dimensions[1]); 
} else {
  define('SCREEN_WIDTH', 120); 
  define('SCREEN_HEIGHT', 120); 
}

if ( SCREEN_WIDTH * SCREEN_HEIGHT <= 26000 ) {
  define('APP_SIZE', 'S');
  $app = array( 'font_size'    => 12,
                'line_height'  => 14,
                'title_indent' => 15,
                'indent'       => 2,
              );
} elseif ( SCREEN_WIDTH * SCREEN_HEIGHT <= 40000) {
  define('APP_SIZE', 'M');
  $app = array( 'font_size'    => 15,
                'line_height'  => 17,
                'title_indent' => 18,
                'indent'       => 3,
              );
} elseif ( SCREEN_WIDTH * SCREEN_HEIGHT <= 100000) {
  define('APP_SIZE', 'L');
  $app = array( 'font_size'    => 18,
                'line_height'  => 20,
                'title_indent' => 21,
                'indent'       => 3,
              );
} elseif ( SCREEN_WIDTH * SCREEN_HEIGHT <= 185000) {
  define('APP_SIZE', 'XL');
  $app = array( 'font_size'    => 23,
                'line_height'  => 27,
                'title_indent' => 21,
                'indent'       => 3,
              );
} else {
  define('APP_SIZE', 'XXL');
  $app = array( 'font_size'    => 30,
                'line_height'  => 40,
                'title_indent' => 21,
                'indent'       => 3,
              );
}

