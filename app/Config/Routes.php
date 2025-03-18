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

/* TagsAjoutController */

$routes->get('/creer_un_tag', 'TagsAjoutController::index', ['filter' => 'FiltreAdmin']);
$routes->post('/createTag', 'TagsAjoutController::createTag', ['filter' => 'FiltreAdmin']);

/* TagsController */

$routes->get('/liste_des_tags', 'TagsController::index', ['filter' => 'FiltreAdmin']);
$routes->get('/liste_des_tags/delete/(:num)', 'TagsController::delete/$1', ['filter' => 'FiltreAdmin']);

/* TagsModifierController */

$routes->get('/modifier_un_tag/(:num)', 'TagsModifierController::index/$1', ['filter' => 'FiltreAdmin']);
$routes->post('/modifier_un_tag/modify/(:num)', 'TagsModifierController::modify/$1', ['filter' => 'FiltreAdmin']);

/* VillesAjoutController */

$routes->get('/ajouter_une_ville', 'VillesAjoutController::index', ['filter' => 'FiltreAdmin']);
$routes->post('/createCity', 'VillesAjoutController::createCity', ['filter' => 'FiltreAdmin']);

/* VillesController */

$routes->get('/liste_des_villes', 'VillesController::index', ['filter' => 'FiltreAdmin']);
$routes->get('/liste_des_villes/delete/(:num)', 'VillesController::delete/$1', ['filter' => 'FiltreAdmin']);

/* VillesModifierController */

$routes->get('/modifier_une_ville/(:num)', 'VillesModifierController::index/$1', ['filter' => 'FiltreAdmin']);
$routes->post('/modifier_une_ville/modify/(:num)', 'VillesModifierController::modify/$1', ['filter' => 'FiltreAdmin']);

/* VoyageController */

$routes->get('/planifier_un_voyage', 'VoyageController::index', ['filter' => 'FiltreUser']);
$routes->post('/createTravel', 'VoyageController::createTravel', ['filter' => 'FiltreUser']);

$routes->get('profil/delete/(:num)', 'VoyageController::delete/$1', ['filter' => 'FiltreUser']);

/* VoyageDetailsController */

$routes->get('details_voyage/(:num)', 'VoyageDetailsController::index/$1', ['filter' => 'FiltreUser']);
$routes->post('/modifyTravel/(:num)', 'VoyageDetailsController::modifyTravel/$1', ['filter' => 'FiltreUser']);

/* WebServiceController */

$routes->get('/api/getTravels', 'WebServiceController::getTravels', ['filter' => 'FiltreAdmin']);
$routes->get('/api/getKeypoints', 'WebServiceController::getKeypoints', ['filter' => 'FiltreAdmin']);
$routes->get('/api/getTags', 'WebServiceController::getTags', ['filter' => 'FiltreAdmin']);
$routes->get('/api/getTravelsByUser/(:num)', 'WebServiceController::getTravelsByUser/$1', ['filter' => 'FiltreAdmin']);
$routes->get('/api/getKeypointsByTag/(:num)', 'WebServiceController::getKeypointsByTag/$1', ['filter' => 'FiltreAdmin']);
$routes->get('/api/getKeypointsByCity/(:alpha)', 'WebServiceController::getKeypointsByCity/$1', ['filter' => 'FiltreAdmin']);