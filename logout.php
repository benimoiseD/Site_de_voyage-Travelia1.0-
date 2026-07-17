<?php
require_once __DIR__ . '/INCLUDE/fonction.php';
secure_session_start();



header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

header('Pragma: no-cache');



$_SESSION = [];



if (ini_get('session.use_cookies')) {

    $params = session_get_cookie_params();

    setcookie(session_name(), '', [

        'expires'  => time() - 42000,

        'path'     => $params['path'],

        'domain'   => $params['domain'],

        'secure'   => $params['secure'],

        'httponly' => $params['httponly'],

        'samesite' => $params['samesite'] ?? 'Lax',

    ]);

}



session_destroy();



header('Location: index.php?deconnecte=1');

exit;





