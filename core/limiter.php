<?php

class Limiter{
    private $prefix = 'limiter_';
    private $cache_ttl = 2592000;
    private $use_session = true;
    public $key = '';
    public $maxattempt = 0;
    public $decaysecond = 60;

    public function __construct($key,$maxattempt,$decaysecond,$use_session=true)
    {
        $this->key = $this->prefix.$key;
        $this->maxattempt = $maxattempt;
        $this->decaysecond = $decaysecond;
        $this->use_session = $use_session;
    }

    public function hit(){
        global $CACHE, $SESSION;

        if($this->use_session){
            $exists = $SESSION->get($this->key);
        }
        else{
            $exists = $CACHE->get($this->key);
        }

        if(!empty($exists)){
            if($this->isMaxAttempt()){
                return false;
            }
            else{
                $limit = $exists;
                $limit['ATTEMPT'] = $limit['ATTEMPT'] + 1;

                if($limit['ATTEMPT'] >= $this->maxattempt){
                    $limit['IS_MAX_ATTEMPT'] = true;
                    $limit['BLOCK_UNTIL'] = time() + $this->decaysecond;
                }
                else{
                    $limit['IS_MAX_ATTEMPT'] = false;
                    $limit['BLOCK_UNTIL'] = 0;
                }

                if($this->use_session){
                    $SESSION->set($this->key,$limit);
                }
                else{
                    $CACHE->save($this->key,$limit,$this->cache_ttl);
                }
            }
        }
        else{
            $limit = array(
                'KEY' => $this->key,
                'ATTEMPT' => 1,
                'IS_MAX_ATTEMPT' => ($this->maxattempt == 0 ? true : false),
                'BLOCK_UNTIL' => ($this->maxattempt == 0 ? (time() + $this->decaysecond) : 0)
            );

            if($limit['ATTEMPT'] >= $this->maxattempt){
                $limit['IS_MAX_ATTEMPT'] = true;
                $limit['BLOCK_UNTIL'] = time() + $this->decaysecond;
            }

            if($this->use_session){
                $SESSION->set($this->key,$limit);
            }
            else{
                $CACHE->save($this->key,$limit,$this->cache_ttl);
            }
        }

        return true;
    }

    public function isBlock(){
        if($this->avaliableIn() == 0){
            return false;
        }
        else{
            return true;
        }
    }

    public function isMaxAttempt(){
        global $CACHE, $SESSION;

        if($this->use_session){
            $exists = $SESSION->get($this->key);
        }
        else{
            $exists = $CACHE->get($this->key);
        }

        if(!empty($exists)){
            if($exists['IS_MAX_ATTEMPT'] OR $exists['ATTEMPT'] >= $this->maxattempt){
                $diff = $exists['BLOCK_UNTIL'] - time();
                if($diff >= 0){
                    return true;
                }
                else{
                    $limit = array(
                        'KEY' => $this->key,
                        'ATTEMPT' => 0,
                        'IS_MAX_ATTEMPT' => ($this->maxattempt == 0 ? true : false),
                        'BLOCK_UNTIL' => ($this->maxattempt == 0 ? (time() + $this->decaysecond) : 0)
                    );
                    if($this->use_session){
                        $SESSION->set($this->key,$limit);
                    }
                    else{
                        $CACHE->save($this->key,$limit,$this->cache_ttl);
                    }
                    return false;
                }
            }
            else{
                return false;
            }
        }
        else{
            return false;
        }
    }

    public function avaliableIn(){
        global $CACHE, $SESSION;

        if($this->use_session){
            $exists = $SESSION->get($this->key);
        }
        else{
            $exists = $CACHE->get($this->key);
        }

        if(!empty($exists)){
            if($this->isMaxAttempt()){
                $diff = $exists['BLOCK_UNTIL'] - time();
                if($diff >= 0){
                    return $diff;
                }
                else{
                    return 0;
                }
            }
            else{
                return 0;
            }
        }
        else{
            return 0;
        }
    }

    public function reset(){
        global $CACHE, $SESSION;

        if($this->use_session){
            $SESSION->del($this->key);
        }
        else{
            $CACHE->delete($this->key);
        }
    }
}

?>