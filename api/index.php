<?php

require 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;


const KEY = "hello-world";

$app = new \Slim\App;


$Jwt = new \Slim\Middleware\JwtAuthentication([
    "path" => "/api",
    "secure" => false,
    "passthroughs"  => ["/api/users/login","/api/users/register"],
    "secret" => KEY,
    "attribute" => "decoded_token_data",
    "algorithm" => ["HS256"],
    "error" => function($response, $args) {
    $data = array('error' => 'erreur' , 'error' => 'AUTO');
    return $response -> withHeader("Content-Type", "application/json")->getBody()->write(json_encode($data));
    }
]);


$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->add($Jwt);

$app->get('/product', 'getProducts');

$app->get('/getClient', 'getClient');
$app->post('/client', 'addClient');
$app->put('/client/{id}', 'updateClient');
$app->delete('/client/{id}', 'deleteClient');
$app->get('/connexion', '');


function login($request, $response, $args){
    $login = "toto";
    $password = "toto";

    $body = $request->getParsedBody();
    $enterLogin = $body['login'];
    $enterPassword = $body['password'];

    if($enterLogin == $login && $enterPassword == $password){
        $issuedAt = time();
        $expiration = $issuedAt + 30;
        $payload = array(
            'login' => $enterLogin,
            'issue' => $issuedAt,
            'expiration' => $expiration
        );

        $jwt = JWT::encode($payload, KEY, "HS256");

        $response = $response->withHeader("Authorization", "Bearer {$jwt}")->withHeader("Content-
        Type", "application/json");

        $data = array('login' => $login, 'password' =>  $password, 'token' => $jwt);
        return $response-> withJson($data);
    }
    $data = array("error" => 'error!', 'token' => "error de login");
    return $response->withJson($data);

}

$app->post('/login',login);

function setHeader($response) {
    return $response->withHeader("Access-Control-Allow-Origin", "*")->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
}

function addClient($request,$response,$args) {
    $body = $request->getParsedBody();
    $client = array();
    $client->firstName = $body['firstname'];
    $client->lastName = $body['lastname'];
    $client->civilite = $body['civilite'];
    $client->tel = $body['tel'];
    $client->city = $body['city'];
    $client->address = $body['address'];
    $client->postalCode = $body['postalCode'];
    $client->country = $body['country'];
    $client->mail = $body['mail'];
    $client->login = $body['login'];
    $client->password = $body['password'];

    return $response->write(json_encode($client));
}

$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $array = [];
    $array ["nom"] = $args ['name'];
    $response->getBody()->write(json_encode ($array));
    return $response;
});


$app->run();
