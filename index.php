<?php
// header_remove();
header("Access-Control-Allow-Origin: http://localhost:8100");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Access-Control-Allow-Headers: Authorization, X-Authorization, Origin, Accept, Content-Type, X-Requested-With, X-HTTP-Method-Override");
// if (isset($_SERVER['HTTP_ORIGIN'])) {
//     header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
//     header('Access-Control-Allow-Credentials: true');
//     header('Access-Control-Max-Age: 86400');
//     header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
//     header("Access-Control-Allow-Headers', 'Authorization, X-Authorization, Origin, Accept, Content-Type, X-Requested-With, X-HTTP-Method-Override");
// }
// if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
//     if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])){
//         header("Access-Control-Allow-Methods: GET, PUT, DELETE, OPTIONS");
//     }
//     if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])){
//         header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
//     }
//     exit(0);
// }

echo json_encode(['oi']);

//require 'webroot' .DIRECTORY_SEPARATOR. 'index.php';