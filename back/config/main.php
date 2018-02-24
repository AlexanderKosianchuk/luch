<?php
return [
  'db' => include('db.php'),
  'redis' => include('db.redis.php'),
  'acl' => include('acl.php'),
  'params' => include('params.php'),
  'components' => include('components.php'),
  'i18n' => [
    'forcedLang' => 'en',
    'langCache' => SITE_ROOT_DIR . '/runtime/langcache/',
    'langFilesDir' => SITE_ROOT_DIR . '/back/lang/'
  ]
];
