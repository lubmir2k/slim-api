<?php

require 'vendor/autoload.php';
include 'bootstrap.php';

use Chatter\Models\Message;
use Chatter\Middleware\Logging as ChatterLogging;
use Chatter\Middleware\Authentication as ChatterAuth;
use Chatter\Middleware\FileFilter;
use Chatter\Middleware\FileMove;
use Chatter\Middleware\ImageRemoveExif;

$config = ['settings' => ['displayErrorDetails' => true]]; $app = new Slim\App($config);
//$app = new \Slim\App();
//$app->add(new ChatterAuth());
$app->add(new ChatterLogging());

$app->group('/v1', function() {
	$app->group('/messages', function() {
		$this->map(['GET'], '', function($request, $response, $args) {
			
			$_message = new Message();
			$messages = $_message->all();
			$payload = [];
			foreach($messages as $message) {
				$payload[$message->id] = $message->output();
			}
			return $response->withStatus(200)->withJson($payload);

		})->setName('get_messages');
	});
});

$filter = new FileFilter();
$removeExif = new ImageRemoveExif();
$move = new FileMove();

$app->post('/messages', function($request, $response, $args) {
	$_message = $request->getParsedBodyParam('message', '');
	
	$imagepath = '';
	
	$message = new Message();
	$message->body = $_message;
	$message->user_id = -1;
	$message->image_url = $request->getAttribute('png_filename');
	$message->save();
	
	if($message->id) {
		$payload = ['message_id' => $message->id,
					'message_uri' => '/messages/' . $message->id,
					'image_url' => $message->image_url];
		return $response->withStatus(201)->withJson($payload);
	} else {
		return $response->withStatus(400);
	}
})->add($filter)->add($removeExif)->add($move);

$app->delete('/messages/{message_id}', function($request, $response, $args) {
	$message = Message::find($args['message_id']);
	$message->delete();
	
	if($message->exists) {
		return $response->withStatus(400);
	} else {
		return $response->withStatus(204);
	}
});

$app->run();