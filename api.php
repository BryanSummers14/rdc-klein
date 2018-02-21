<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . 'configs.php';

$klein = new \Klein\Klein();

//================
// Global Setup
//================
$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg);
        $klein->service()->back();
    });
    // Global db setup, used in every call
    $app->db = new PDO($dsn, $db_username, $db_password, $opt);

});

// Get all addresses
$klein->respond('GET', '/addresses', function($request, $response, $service, $app) {

    $db_results = $app->db->query('SELECT * FROM addresses');
    return $response->json($db_results);

});

// Get address by id
$klein->respond('GET', '/address', function($request, $response, $service, $app) {
    
    $id = $request->param('id');
    
    $stmnt = $app->db->prepare("SELECT * from addresses where address_id=?");
    $db_results = $stmnt->execute([$id]);
    return $response->json($db_results);
    
});

// Add new address
$klein->respond('POST', '/address', function($request, $response, $service, $app) {

    $service->validateParam('street')->notNull();
    $service->validateParam('city')->notNull();
    $service->validateParam('state')->notNull();
    $service->validateParam('zip')->notNull();

    $street = $request->param('street');
    $city = $request->param('city');
    $state = $request->param('state');
    $zip = $request->param('zip');

    $stmnt = $app->db->prepare("INSERT INTO addresses (street, city, state, zip) VALUES (?, ?, ?, ?)");
    $db_results = $stmnt->execute([$street, $city, $state, $zip]);
    return $response->json($db_results);

});

// Get address by name?
$klein->respond('GET', '/addresses/[:name]', function($request, $response, $service, $app) {

    $name = $request->name;
    
    $stmnt = $app->db->prepare("SELECT * FROM addresses where street LIKE ?");
    $db_results = $stmnt->execute(['%'.$name.'%']);
    return $response->json($db_results);
    
});

// Get address by zip
$klein->respond('GET', '/addresses/[:zip]', function($request, $response, $service, $app) {

    $stmnt = $app->db->prepare("SELECT * FROM addresses where zip=?");
    $db_results = $stmnt->execute([$request->zip]);
    return $response->json($db_results);

});


$klein->dispatch();
