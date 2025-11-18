<?php
/**
 * KaleidoChrome - ログアウト処理
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

logoutAdmin();
redirect(ADMIN_PATH . '/index.php');
