<?php

require 'vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Firebase\JWT\JWT;


const KEY = "hello-world";

$app = new \Slim\App;


$keyJwt = new \Slim\Middleware\JwtAuthentication([
    "path" => "/api",
    "secure" => false,
    "passthroughs"  => ["/api/users/login","/api/users/register"],
    "secret" => KEY,
    "attribute" => "decoded_token_data",
    "algorithm" => ["HS256"],
    "error" => function($response, $args) {
    $data = array('error' => 'erreur' , 'error' => "auth");
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

$app->add($keyJwt);

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

function addClient($request,$response,$args) {
    $body = $request->getParsedBody();
    $client = array();
    $client->nom = $body['nom'];
    $client->prenom = $body['prenom'];
    $client->civilite = $body['civilite'];
    $client->telephone = $body['telephone'];
    $client->ville = $body['ville'];
    $client->adresse = $body['adresse'];
    $client->cp = $body['cp'];
    $client->pays = $body['pays'];
    $client->email = $body['email'];
    $client->login = $body['login'];
    $client->password = $body['password'];

    return $response->write(json_encode($client));
}



$app->run();
