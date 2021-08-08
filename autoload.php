<?php

/*
 * Session addon for Bear Framework
 * https://github.com/bearframework/session-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

BearFramework\Addons::register('bearframework/session-addon', __DIR__, [
    'require' => [
        'ivopetkov/locks-bearframework-addon'
    ]
]);
