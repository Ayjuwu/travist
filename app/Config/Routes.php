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

/* profileController */

$routes->get('/profil', 'profileController::index', ['filter' => 'FiltreUser']);

/* panelController */

$routes->get('/panel_administrateur', 'panelController::index', ['filter' => 'FiltreAdmin']);

/* KeyPointAddController */

$routes->get('/creer_un_lieu', 'KeyPointAddController::index', ['filter' => 'FiltreAdmin']);
$routes->post('/createKeyPoint', 'KeyPointAddController::createKeyPoint', ['filter' => 'FiltreAdmin']);

/* KeyPointsListController */

$routes->get('/liste_des_lieux', 'KeyPointsListController::index', ['filter' => 'FiltreAdmin']);
$routes->get('/liste_des_lieux/delete/(:num)', 'KeyPointsListController::delete/$1', ['filter' => 'FiltreAdmin']);

/* KeyPointsModifyController */

$routes->get('/modifier_un_lieu/(:num)', 'KeyPointModifyController::index/$1', ['filter' => 'FiltreAdmin']);
$routes->post('/modifier_un_lieu/modify/(:num)', 'KeyPointModifyController::modify/$1', ['filter' => 'FiltreAdmin']);


$routes->get('/creer_un_tag', 'TagAddController::index', ['filter' => 'FiltreAdmin']);
$routes->post('/createTag', 'TagAddController::createTag', ['filter' => 'FiltreAdmin']);

$routes->get('/liste_des_tags', 'TagsListController::index', ['filter' => 'FiltreAdmin']);
$routes->get('/liste_des_tags/delete/(:num)', 'TagsListController::delete/$1', ['filter' => 'FiltreAdmin']);

$routes->get('/modifier_un_tag/(:num)', 'TagModifyController::index/$1', ['filter' => 'FiltreAdmin']);
$routes->post('/modifier_un_tag/modify/(:num)', 'TagModifyController::modify/$1', ['filter' => 'FiltreAdmin']);