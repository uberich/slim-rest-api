<?php
// Application middleware

// $app->add(new \Slim\Csrf\Guard);
$app->add($container->get('csrf'));