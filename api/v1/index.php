<?php
define('BASEPATH', 1);

require '../../vendor/autoload.php';

require_once 'utils/sessionUtils.php';
require_once 'utils/passwordUtils.php';
require_once 'utils/requestUtils.php';
require_once 'mail/iMailer.php';
require_once 'mail/SimpleMailer.php';
require_once 'mail/MandrillMailer.php';

require '../config.php';

error_reporting(E_ALL ^ E_WARNING); //disable warnings that ruin JSON formats

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    try{
        $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
            $db['user'], $db['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        print "Error!: " . $e->getMessage() . "<br/>";
        die();
    }
    return $pdo;
};

$container['mandrill'] = function ($c) {
    $config = $c['settings']['mandrill'];
    $client = new Mandrill($config['apiKey']);
    return $client;
};

$container['mailer'] = function ($c) {
    $config = $c['settings']['mandrill'];
    if (isset($config['apiKey']) && !empty($config['apiKey'])){
        return new MandrillMailer($c, new Mandrill($config['apiKey']));
    } 
    return new SimpleMailer($c);
};

require_once 'authentication.php';

require_once 'profile.php';

require_once 'password.php';

require_once 'tables.php';

require_once 'upload.php';

require_once 'email.php';

$app->run();
?>