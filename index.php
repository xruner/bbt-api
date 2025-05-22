<?php
$allowedOrigins = [
    "http://a2.xruner.tk",
    "https://a2.xruner.tk"
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);
} else {
    header("Access-Control-Allow-Origin: null");
}
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/jwt.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// 路由定义
if ($uri[1] === 'api') {
    $endpoint = $uri[2] ?? '';
    
    switch ($endpoint) {
        case 'timeslots':
            require __DIR__ . '/routes/timeslots.php';
            break;
        case 'appointments':
            require __DIR__ . '/routes/appointments.php';
            break;
        case 'notifications':
            require __DIR__ . '/routes/notifications.php';
            break;
        case 'settings':
            require __DIR__ . '/routes/settings.php';
            break;
        default:
            header("HTTP/1.1 404 Not Found");
            exit();
    }
} else {
    header("HTTP/1.1 404 Not Found");
    exit();
}
?>