<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', 'Home::index');

/* inscriptionController */

$routes->get('/inscription', 'inscriptionController::index');
$routes->post('/createAccount', 'inscriptionController::createAccount');

/* connexionController */

$routes->get('/connexion', 'connexionController::index');
$routes->get('/connexion/disconnect', 'connexionController::disconnect');
$routes->post('/loginAccount', 'connexionController::loginAccount');

/* profilController */

$routes->get('/profil', 'profilController::index', ['filter' => 'FiltreUser']);

/* panelController */

$routes->get('/panel_administrateur', 'panelController::index', ['filter' => 'FiltreAdmin']);

/* lieuxAjoutController */

$routes->get('/creer_un_lieu', 'lieuxAjoutController::index', ['filter' => 'FiltreAdmin']);
$routes->post('/createKeyPoint', 'lieuxAjoutController::createKeyPoint', ['filter' => 'FiltreAdmin']);

/* lieuxController */

$routes->get('/liste_des_lieux', 'lieuxController::index', ['filter' => 'FiltreAdmin']);
$routes->get('/liste_des_lieux/delete/(:num)', 'lieuxController::delete/$1', ['filter' => 'FiltreAdmin']);

/* lieuxModifierController */

$routes->get('/modifier_un_lieu/(:num)', 'lieuxModifierController::index/$1', ['filter' => 'FiltreAdmin']);
$routes->post('/modifier_un_lieu/modify/(:num)', 'lieuxModifierController::modify/$1', ['filter' => 'FiltreAdmin']);


$routes->get('/creer_un_tag', 'TagsAjoutController::index', ['filter' => 'FiltreAdmin']);
$routes->post('/createTag', 'TagsAjoutController::createTag', ['filter' => 'FiltreAdmin']);

$routes->get('/liste_des_tags', 'TagsController::index', ['filter' => 'FiltreAdmin']);
$routes->get('/liste_des_tags/delete/(:num)', 'TagsController::delete/$1', ['filter' => 'FiltreAdmin']);

$routes->get('/modifier_un_tag/(:num)', 'TagsModifierController::index/$1', ['filter' => 'FiltreAdmin']);
$routes->post('/modifier_un_tag/modify/(:num)', 'TagsModifierController::modify/$1', ['filter' => 'FiltreAdmin']);

/* VoyageAjoutController */

$routes->get('/planifier_un_voyage', 'VoyageAjoutController::index', ['filter' => 'FiltreUser']);
$routes->post('/createTravel', 'VoyageAjoutController::createTravel', ['filter' => 'FiltreUser']);