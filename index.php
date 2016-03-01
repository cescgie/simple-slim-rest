<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'dbconfig.php';

$app = new Slim\App(['settings' => ['displayErrorDetails' =>true]]);

$app->get('/', function (Request $request,  Response $response) {
    $response->write("home");
    return $response;
});

$app->get('/hello/{name}', function (Request $request,  Response $response, $args = []) {
    $response->write("Hello, " . $args['name']);
    return $response;
});

$app->get('/getScore/{id}', function (Request $request,  Response $response, $args = []) {
    $id = $args['id'];

    try
    {
        $db = getDB();
        $sth = $db->prepare("SELECT * FROM students WHERE student_id = :id");
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();
        $student = $sth->fetch(PDO::FETCH_OBJ);

        if($student) {
            $response->withStatus(200)
                     ->withHeader('Content-Type', 'application/json')
                     ->write(json_encode($student));
            $db = null;
        } else {
            $status = ['status'=>'error'];
            $response->withStatus(404)
                     ->withHeader('Content-Type', 'application/json')
                     ->write(json_encode($status));
        }

    } catch(PDOException $e) {
        $status = ['status'=>'error'];
        $response->withStatus(404)
                 ->withHeader('Content-Type', 'application/json')
                 ->write(json_encode($status));
    }
    return $response;
});

$app->get('/getAll', function (Request $request,  Response $response) {
    try
    {
        $db = getDB();
        $sth = $db->prepare("SELECT * FROM students");
        $sth->execute();
        $student = $sth->fetchAll(PDO::FETCH_ASSOC);

        if($student) {
            $response->withStatus(200)
                     ->withHeader('Content-Type', 'application/json')
                     ->write(json_encode($student));
            $db = null;
        } else {
          $status = ['status'=>'error'];
          $response->withStatus(404)
                   ->withHeader('Content-Type', 'application/json')
                   ->write(json_encode($status));
      }

    } catch(PDOException $e) {
        $status = ['status'=>'error'];
        $response->withStatus(404)
                 ->withHeader('Content-Type', 'application/json')
                 ->write(json_encode($status));
    }
    return $response;
});

$app->post('/updateScore', function(Request $request,  Response $response) {

    $allPostPutVars = $request->getParsedBody();
    $score = $allPostPutVars['score'];
    $id = $allPostPutVars['id'];

    try
    {
        $db = getDB();

        $sth = $db->prepare("UPDATE students
            SET score = :score
            WHERE student_id = :id");

        $sth->bindParam(':score', $score, PDO::PARAM_INT);
        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();

        $status = ['status'=>'success'];
        $response->withStatus(200)
                 ->withHeader('Content-Type', 'application/json')
                 ->write(json_encode($status));
        $db = null;

    } catch(PDOException $e) {
      $status = ['status'=>'error'];
      $response->withStatus(404)
               ->withHeader('Content-Type', 'application/json')
               ->write(json_encode($status));
    }
    return $response;
});

$app->get('/delete/{id}', function (Request $request,  Response $response, $args = []) {
    $id = $args['id'];
    try
    {
        $db = getDB();

        $sth = $db->prepare("DELETE FROM students WHERE student_id = :id LIMIT 1");

        $sth->bindParam(':id', $id, PDO::PARAM_INT);
        $sth->execute();

        $status = ['status'=>'success'];
        $response->withStatus(200)
                 ->withHeader('Content-Type', 'application/json')
                 ->write(json_encode($status));
        $db = null;

    } catch(PDOException $e) {
      $status = ['status'=>'error'];
      $response->withStatus(404)
               ->withHeader('Content-Type', 'application/json')
               ->write(json_encode($status));
    }
    return $response;
});

$app->run();
