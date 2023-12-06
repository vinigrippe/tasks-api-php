<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;

require 'utils/helpers.php';

function generate_token($username, $secretKey) {
    $payload = [
        'username' => $username,
        'iat' => time(),
        'exp' => time() + (60*60)
    ];

    $algorithm = 'HS256'; 
    $token = JWT::encode($payload, $secretKey, $algorithm);
    return $token;
}

function validate_token($token, $pdo){
    $fetch = $pdo->prepare('SELECT * FROM tokens WHERE token = ?');
    $fetch->execute([$token]);
    $valid = $fetch->fetch(PDO::FETCH_ASSOC);

    if ($valid) {
       return true;
    } else {
       return false;
    }
}

function loginRoute($app, $pdo) {  
    $app->post('/login', function (Request $request, Response $response, array $args) use ($pdo) {
        $data = $request->getParsedBody();
        $username = $data['username'];
        $password = $data['password'];
        $hashed_password = hash_string($password);

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND password = ?');
        $stmt->execute([$username, $hashed_password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
          $secretKey = 'your-secret-key';
          $createToken = generate_token($username, $secretKey);
          $insertToken = $pdo->prepare('INSERT INTO tokens (userId, token) VALUES (?, ?)');
          $insertToken->execute([$user['id'], $createToken]);
          
          unset($user['password']);
          $response = $response->withJson([
              'user' => $user,
              'token' => $createToken
          ]);
        } else { 
          $response = $response->withStatus(401);
          $response = $response->withJson(['message' => 'login failed']);
        }

        return $response;
    });

    $app->post('/logout', function (Request $request, Response $response, array $args) use ($pdo) {
        $authorization = $request->getHeaderLine('Authorization');
        $token = explode(' ', $authorization)[1];

        if ($authorization && $token && validate_token($token, $pdo)){
            $stmt = $pdo->prepare('DELETE FROM tokens WHERE token = ?');
            $stmt->execute([$token]);

            $response = $response->withStatus(200);
            $response = $response->withJson(['message' => 'logout successful']);
        } else {
            $response = $response->withStatus(401);
            $response = $response->withJson(['message' => 'logout failed']);
        }

        return $response;
    });
 }