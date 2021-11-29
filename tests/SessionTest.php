<?php

/*
 * Session addon for Bear Framework
 * https://github.com/bearframework/session-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

/**
 * @runTestsInSeparateProcesses
 */
class SessionTest extends BearFramework\AddonTests\PHPUnitTestCase
{

    /**
     * 
     */
    public function testBasics()
    {
        $app = $this->getApp();

        $app->session->set('key1', 'value1');

        // Test get()
        $value = $app->session->get('key1');
        $this->assertEquals($value, 'value1');

        // Test delete()
        $app->session->delete('key1');
        $value = $app->session->get('key1');
        $this->assertEquals($value, null);

        // Test deleteAll()
        $app->session->set('key1', 'value1');
        $app->session->set('key2', 'value2');
        $value = $app->session->get('key1');
        $this->assertEquals($value, 'value1');
        $value = $app->session->get('key2');
        $this->assertEquals($value, 'value2');
        $app->session->deleteAll();
        $value = $app->session->get('key1');
        $this->assertEquals($value, null);
        $value = $app->session->get('key2');
        $this->assertEquals($value, null);
    }
}
