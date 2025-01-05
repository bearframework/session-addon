<?php

/*
 * Session addon for Bear Framework
 * https://github.com/bearframework/session-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

use BearFramework\App;

$app = App::get();
$context = $app->contexts->get(__DIR__);

$context->classes
    ->add('BearFramework\Session', 'classes/Session.php');

$cookieName = 'bfsesid';

$app->shortcuts
    ->add('session', function () use ($app, $cookieName) {
        return new \BearFramework\Session($app->request->cookies->getValue($cookieName));
    });

$app
    ->addEventListener('beforeSendResponse', function (App\BeforeSendResponseEventDetails $details) use ($app, $cookieName): void {
        $response = $details->response;
        $cookieSessionID = $app->request->cookies->getValue($cookieName);
        $currentSessionID = $app->session->id;
        if ($currentSessionID === null) {
            if ($cookieSessionID !== null) {
                $response->cookies->delete($cookieName);
            }
        } else {
            if ($cookieSessionID !== $currentSessionID) {
                $response->cookies->set($response->cookies->make($cookieName, $currentSessionID));
            }
        }
    });
