<?php
namespace RedAlgae\Core;

class Limiter {
    private $prefix = 'limiter_';
    private $cache_ttl = 2592000;
    private $use_session = true;
    public $key = '';
    public $maxattempt = 0;
    public $decaysecond = 60;

    public function __construct($key, $maxattempt, $decaysecond, $use_session = true) {
        $this->key = $this->prefix . $key;
        $this->maxattempt = $maxattempt;
        $this->decaysecond = $decaysecond;
        $this->use_session = $use_session;
    }

    private function getData() {
        global $CACHE, $SESSION;
        return $this->use_session ? $SESSION->get($this->key) : $CACHE->get($this->key);
    }

    private function saveData($data) {
        global $CACHE, $SESSION;
        if ($this->use_session) {
            $SESSION->set($this->key, $data);
        } else {
            $CACHE->save($this->key, $data, $this->cache_ttl);
        }
    }

    private function deleteData() {
        global $CACHE, $SESSION;
        if ($this->use_session) {
            $SESSION->del($this->key);
        } else {
            $CACHE->delete($this->key);
        }
    }

    public function hit() {
        $limit = $this->getData() ?: [
            'KEY' => $this->key,
            'ATTEMPT' => 0,
            'IS_MAX_ATTEMPT' => false,
            'BLOCK_UNTIL' => 0
        ];

        if ($this->isMaxAttempt()) {
            return false;
        }

        $limit['ATTEMPT']++;
        if ($limit['ATTEMPT'] >= $this->maxattempt) {
            $limit['IS_MAX_ATTEMPT'] = true;
            $limit['BLOCK_UNTIL'] = time() + $this->decaysecond;
        }

        $this->saveData($limit);
        return true;
    }

    public function isMaxAttempt() {
        $limit = $this->getData();
        if (empty($limit) || !$limit['IS_MAX_ATTEMPT']) {
            return false;
        }

        if ($limit['BLOCK_UNTIL'] > time()) {
            return true;
        }

        $this->reset();
        return false;
    }

    public function availableIn() {
        $limit = $this->getData();
        return $limit && $this->isMaxAttempt() ? max(0, $limit['BLOCK_UNTIL'] - time()) : 0;
    }

    public function isBlock() {
        return $this->availableIn() > 0;
    }

    public function reset() {
        $this->deleteData();
    }
}
?>
