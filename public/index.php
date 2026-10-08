<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

redirect($auth->isLoggedIn() ? 'home.php' : 'login.php');
