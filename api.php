<?php
require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . 'configs.php';

$klein = new \Klein\Klein();

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    // Handle exceptions => flash the message and redirect to the referrer
    $klein->onError(function ($klein, $err_msg) {
        $klein->service()->flash($err_msg);
        $klein->service()->back();
    });

    $app->db = new PDO($dsn, $db_username, $db_password, $opt);

});
// Remove this (just for reference)
$klein->respond('POST', '/users/[i:id]/edit', function ($request, $response, $service, $app) {
    // Quickly validate input parameters
    $service->validateParam('username', 'Please enter a valid username')->isLen(5, 64)->isChars('a-zA-Z0-9-');
    $service->validateParam('password')->notNull();

    $app->db->query(); // etc.

    // Add view properties and helper methods
    $service->title = 'foo';
    $service->escape = function ($str) {
        return htmlentities($str); // Assign view helpers
    };

    $service->render('myview.html');
});

$klein->respond('GET', '/addresses', function($request, $response) {

    $db_results = $app->db->query('SELECT * FROM addresses');
    return $response->json($db_results);

});

$klein->respond('POST', '/address', function($request, $response) {

    $street = $request->param('street');
    $city = $request->param('city');
    $state = $request->param('state');
    $zip = $request->param('zip');

    $stmnt = $app->db->prepare("INSERT INTO addresses VALUES (?, ?, ?, ?");
    $db_results = $stmnt->execute([$street, $city, $state, $zip]);
    return $response->json($db_results);

});

$klein->respond('GET', '/addresses/[:zip]', function($request, $response) {

    $stmnt = $app->db->prepare("SELECT * FROM addresses where zip=?");
    $db_results = $stmnt->execute($request->zip);
    return $response->json($db_results);

});

$klein->dispatch();
