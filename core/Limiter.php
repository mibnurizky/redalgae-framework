<?php
namespace RedAlgae\Core;

class Limiter {
    private $prefix = 'limiter_';
    private $cache_ttl = 2592000;
    private $use_session = true;
    public $key = '';
    public $maxattempt = 0;
    public $decaysecond = 60;
    private $session = null;
    private $cache = null;

    public function __construct($key, $maxattempt, $decaysecond, $use_session = false) {
        $this->key = $this->prefix . $key;
        $this->maxattempt = $maxattempt;
        $this->decaysecond = $decaysecond;
        $this->use_session = $use_session;
        $this->session = new Session(true);
        $this->cache = new Cache();
    }

    private function getData() {
        return $this->use_session ? $this->session->get($this->key) : $this->cache->get($this->key);
    }

    private function saveData($data) {
        if ($this->use_session) {
            $this->session->set($this->key, $data);
        } else {
            $this->cache->save($this->key, $data, $this->cache_ttl);
        }
    }

    private function deleteData() {
        if ($this->use_session) {
            $this->session->del($this->key);
        } else {
            $this->cache->delete($this->key);
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
