<?php

/*
 * Session addon for Bear Framework
 * https://github.com/bearframework/session-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace BearFramework;

use BearFramework\App;

/**
 * 
 */
class Session
{

    /**
     * 
     * @var string|null
     */
    public $id = null;

    /**
     * 
     * @param string $id
     */
    public function __construct(string $id = null)
    {
        $this->id = $id;
    }

    /**
     * 
     * @param string $dataKey
     * @return array|null
     */
    private function getData(string $dataKey): ?array
    {
        $app = App::get();
        $data = $app->data->getValue($dataKey);
        $data = $data !== null ? json_decode($data, true) : null;
        if (is_array($data)) {
            return $data;
        }
        return null;
    }

    /**
     * 
     * @param string $dataKey
     * @param array $data
     * @return void
     */
    private function setData(string $dataKey, array $data): void
    {
        $app = App::get();
        if (empty($data)) {
            $app->data->delete($dataKey);
        } else {
            $app->data->setValue($dataKey, json_encode($data));
        }
    }

    /**
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->prepareSessionID();
        $app = App::get();
        $dataKey = $this->getDataKey($this->id);
        $app->locks->acquire($dataKey);
        $data = $this->getData($dataKey);
        $data[$key] = $value;
        $this->setData($dataKey, $data);
        $app->locks->release($dataKey);
    }

    /**
     * 
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if ($this->id === null) {
            return null;
        }
        $dataKey = $this->getDataKey($this->id);
        $data = $this->getData($dataKey);
        return isset($data[$key]) ? $data[$key] : null;
    }

    /**
     * 
     * @param string $key
     * @return void
     */
    public function delete(string $key): void
    {
        if ($this->id === null) {
            return;
        }
        $app = App::get();
        $dataKey = $this->getDataKey($this->id);
        $app->locks->acquire($dataKey);
        $data = $this->getData($dataKey);
        if (isset($data[$key])) {
            unset($data[$key]);
            $this->setData($dataKey, $data);
        }
        $app->locks->release($dataKey);
    }

    /**
     * 
     * @return void
     */
    public function deleteAll(): void
    {
        if ($this->id === null) {
            return;
        }
        $app = App::get();
        $dataKey = $this->getDataKey($this->id);
        $app->locks->acquire($dataKey);
        $this->setData($dataKey, []);
        $app->locks->release($dataKey);
    }

    /**
     * 
     * @return void
     */
    private function prepareSessionID()
    {
        if ($this->id === null) {
            $app = App::get();
            for ($i = 0; $i < 1000; $i++) {
                $sessionID = '';
                for ($j = 0; $j < 45; $j++) {
                    $sessionID .= base_convert(rand(0, 35), 10, 36);
                }
                $dataKey = $this->getDataKey($sessionID);
                if (!$app->data->exists($dataKey)) {
                    $this->id = $sessionID;
                    break;
                }
            }
            if ($this->id === null) {
                throw new \Exception('Too much retries');
            }
        }
    }

    /**
     * 
     * @param string $sessionID
     * @return string
     */
    private function getDataKey(string $sessionID): string
    {
        $md5SessionID = md5($sessionID);
        return '.temp/session/' . substr($md5SessionID, 0, 2) . '/' . $md5SessionID;
    }

    /**
     * 
     * @return void
     */
    public function lock(): void
    {
        $this->prepareSessionID();
        $app = App::get();
        $app->locks->acquire('session-lock-' . $this->id);
    }

    /**
     * 
     * @return void
     */
    public function unlock(): void
    {
        if ($this->id !== null) {
            $app = App::get();
            $app->locks->release('session-lock-' . $this->id);
        }
    }
}
