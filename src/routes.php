<?php
// Routes
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/signup', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("render-signup-page '/sigup' route");

    // CSRF token name and value
    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $name = $request->getAttribute($nameKey);
    $value = $request->getAttribute($valueKey);

    // Render signup  view
    return $this->renderer->render($response, 'signup.phtml', ["nameKey"=>$nameKey,"valueKey"=>$valueKey,"name"=>$name,"value"=>$value]);
});

$app->get('/login', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("render-login-page '/login' route");

    // CSRF token name and value
    $nameKey = $this->csrf->getTokenNameKey();
    $valueKey = $this->csrf->getTokenValueKey();
    $name = $request->getAttribute($nameKey);
    $value = $request->getAttribute($valueKey);
    
    // Render login  view
    return $this->renderer->render($response, 'login.phtml', ["nameKey"=>$nameKey,"valueKey"=>$valueKey,"name"=>$name,"value"=>$value]);
});

$app->get('/userprofile', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("render-user-profile-view '/userprofile' route");

    // Render userprofile  view
    return $this->renderer->render($response, 'userprofile.phtml', $args);
});

// User Registration and login

$app->post('/signup', function(Request $request, Response $response) {
    $data = $request->getParsedBody();
    $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
    $age = filter_var($data['age'], FILTER_SANITIZE_STRING);  
    $gender = filter_var($data['gender'], FILTER_SANITIZE_STRING);
    $country = filter_var($data['country'], FILTER_SANITIZE_STRING);  
    $email = filter_var($data['email'], FILTER_SANITIZE_STRING);
    $password = filter_var($data['password'], FILTER_SANITIZE_STRING);             
    
    $response->withHeader('Content-Type', 'application/json');
    try {
        $db = getDB();
        $sth = $db->prepare("select count(*) as count from user WHERE email=:email");
        $sth->bindParam(':email', $email, PDO::PARAM_INT);
        $sth->execute();
        $row = $sth->fetch();

        if($row['count']>0) {
            $output = array(
            'status'=>"0",
            'operation'=>"user already registered"
            );
            echo json_encode($output);
            $db = null;
            return;
        }
        else {
            $sth = $db->prepare("INSERT INTO user (name,age,gender,country,email,password)
            VALUES(:name,:age,:gender,:country,:email,:password)");
            $sth->bindParam(':name', $name, PDO::PARAM_INT);
            $sth->bindParam(':age', $age, PDO::PARAM_INT);
            $sth->bindParam(':gender', $gender, PDO::PARAM_INT);
            $sth->bindParam(':country', $country, PDO::PARAM_INT);
            $sth->bindParam(':email', $email, PDO::PARAM_INT);
            $sth->bindParam(':password', $password, PDO::PARAM_INT);
            $sth->execute();
            $output = array(
            'status'=>"1",
            'operation'=>"success"
            );
            echo json_encode($output);
            $db = null;
            return $response->withRedirect("/userprofile");
        }
    }
    catch(Exception $ex) {
        echo $ex;
    }
});

$app->post('/login', function(Request $request, Response $response) {
    $data = $request->getParsedBody();
    $email= filter_var($data['email'], FILTER_SANITIZE_STRING);
    $password= filter_var($data['password'], FILTER_SANITIZE_STRING);    
    
    try {
        $db = getDB();
        $sth = $db->prepare("select count(*) as count from user WHERE email=:email AND
        password=:password");
        $sth->bindParam(':password', $password, PDO::PARAM_INT);
        $sth->bindParam(':email', $email, PDO::PARAM_INT);
        $sth->execute();
        $row = $sth->fetch();
        $response->withHeader('Content-Type', 'application/json');        

        if($row['count']>0) {
            $output = array(
            'status'=>"1",
            'login'=>"sucess",
            );
            return $response->withRedirect("/userprofile");
        }
        else {
            $output = array(
            'status'=>"0",
            'login'=>"fail",
            );
        }
    }
    catch(Exception $ex) {
        $output = array(
        'status'=>"2",
        'login'=>"error",
        );
    }

    // $object = (object) $output;

    echo json_encode($output);    
    $db = null;
});

//Get mysql database connection

function getDB() {
     $dbhost = "localhost";
     $dbuser = "root";
     $dbpass = "people";
     $dbname = "userdb";
     $mysql_conn_string = "mysql:host=$dbhost;dbname=$dbname";
     $dbConnection = new PDO($mysql_conn_string, $dbuser, $dbpass);
     $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     return $dbConnection;
 }
