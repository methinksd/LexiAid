<?php
header('Content-Type: application/json');
echo json_encode([
    'mysqli' => extension_loaded('mysqli'),
    'mysql' => extension_loaded('mysql'), 
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'extensions' => array_filter(get_loaded_extensions(), function($ext) {
        return stripos($ext, 'mysql') !== false || stripos($ext, 'mysqli') !== false;
    })
]);
?>
