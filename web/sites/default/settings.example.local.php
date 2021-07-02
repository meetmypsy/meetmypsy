<?php

$databases['default']['default'] = array (
  'database' => 'mmp',
  'username' => 'root',
  'password' => 'MYSTRONGPASSWORD',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);

$settings['trusted_host_patterns'] = [
  '^www\.mmp\.com$',
];

