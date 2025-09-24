<?php

class Session{

    private $prefix = '_amoeba_';
    private $table = '_amoeba_session';
    private $usedb = false;
    private $sessid = '';

    public function __construct($usedb=false){
        $app = new App();
        $this->usedb = $app->session_db;
        if($this->usedb){
            if(!empty($_COOKIE[$this->prefix.'cookie'])){
                $this->sessid = $_COOKIE[$this->prefix.'cookie'];
            }
            else{
                $sessid = $this->prefix.generateID().'_'.random_characters(10);
                $this->sessid = $sessid;
                setcookie($this->prefix.'cookie',$this->sessid,['httponly' => true, 'secure' => true, "samesite" => "Lax"]);
            }
        }
    }

    public function set($id,$data,$ttl=0){
        $id = $this->prefix.$id;
        if($this->usedb){
            $db = new Database();
            $this->session_db_check_table();
            $session = $db->select("SELECT * FROM ".$this->table." WHERE ID = '".$this->sessid."'");
            $sessiondata = array();
            if(count($session) > 0){
                if($ttl == 0){
                    $expired = 0;
                }
                else{
                    $expired = time() + $ttl;
                }

                $arSession = array();
                $sessiondata = unserialize(getDecrypt($session[0]['SESSION_DATA']));
                foreach($sessiondata as $key => $row){
                    $arSession[$key] = $row;
                }

                $arSession[$id] = array(
                    'DATA' => $data,
                    'EXPIRED' => $expired,
                    'TTL' => $ttl
                );

                $sessiondata = $arSession;
                $db->update($this->table,['SESSION_DATA' => setEncrypt(serialize($sessiondata))],"WHERE ID = '".$this->sessid."'");
            }
            else{
                if($ttl == 0){
                    $expired = 0;
                }
                else{
                    $expired = time() + $ttl;
                }

                $db->insert($this->table,array(
                    'ID' => $this->sessid,
                    'SESSION_DATA' => setEncrypt(serialize([$id => ['DATA' => $data, 'EXPIRED' => $expired, 'TTL' => $ttl]])),
                    'CREATED_TIME' => time()
                ));
            }

            $this->update_last_use();
            $this->delete_not_use();
        }
        else{
            $_SESSION[$id] = $data;
        }

        return true;
    }

    public function get($id,$auto_extend_ttl = false){
        $idori = $id;
        $id = $this->prefix.$id;
        if($this->usedb){
            $db = new Database();
            $this->session_db_check_table();
            $session = $db->select("SELECT * FROM ".$this->table." WHERE ID = '".$this->sessid."'");
            if(count($session) > 0){
                $sessiondata = unserialize(getDecrypt($session[0]['SESSION_DATA']));
                $this->update_last_use();
                $this->delete_not_use();

                if(isset($sessiondata[$id])) {
                    if (isset($sessiondata[$id]['EXPIRED'])) {
                        if ($sessiondata[$id]['EXPIRED'] == 0) {
                            return $sessiondata[$id]['DATA'];
                        } elseif ($sessiondata[$id]['EXPIRED'] > 0) {
                            if (time() <= $sessiondata[$id]['EXPIRED']) {
                                if ($auto_extend_ttl) {
                                    $this->set($idori, $sessiondata[$id]['DATA'], $sessiondata[$id]['TTL']);
                                }
                                return $sessiondata[$id]['DATA'];
                            } else {
                                $this->del($id);
                            }
                        }
                    }
                }
            }

            $this->update_last_use();
            $this->delete_not_use();
            return "";
        }
        else{
            return isset($_SESSION[$id]) ? $_SESSION[$id] : "";
        }
    }

    public function del($id){
        $id = $this->prefix.$id;
        if($this->usedb){
            $db = new Database();
            $this->session_db_check_table();
            $session = $db->select("SELECT * FROM ".$this->table." WHERE ID = '".$this->sessid."'");
            if(count($session) > 0){
                $sessiondata = unserialize(getDecrypt($session[0]['SESSION_DATA']));
                unset($sessiondata[$id]);
                $db->update($this->table,['SESSION_DATA' => setEncrypt(serialize($sessiondata))],"WHERE ID = '".$this->sessid."'");
            }
        }
        else{
            unset($_SESSION[$id]);
        }

        $this->update_last_use();
        $this->delete_not_use();
    }

    public function destroy(){
        if($this->usedb){
            $this->session_db_check_table();
            $db = new Database();
            $db->delete($this->table,"WHERE ID = '".$this->sessid."'");
        }
        else{
            unset($_SESSION);
        }
    }

    public function flash_set($key,$data){
        $key = 'amoeba_flash_'.$key;
        $this->set($key,$data);
        return true;
    }

    public function flash_get($key){
        $key = 'amoeba_flash_'.$key;
        $data = $this->get($key);
        $this->del($key);
        return $data;
    }

    public function merge($id,$data){
        $exist = $this->get($id);
        if(!empty($exist)){
            if(is_array($exist)){
                if(is_array($data)){
                    $exist = array_merge($exist,$data);
                    $this->set($id,$exist);
                }
                else{
                    $exist[] = $data;
                    $this->set($id,$data);
                }
            }
            else{
                $this->set($id,$data);
            }
        }
        else{
            $this->set($id,$data);
        }
    }

    public function extendLife($id){
        $this->get($id,true);
    }

    public function session_db_check_table(){
        $db = new Database();
        $cache = new Cache();

        $table_exists = $cache->get('__amoeba_session_table__');
        if($table_exists){
            return true;
        }
        $data = $db->select("
            SELECT table_name FROM information_schema.tables
            WHERE table_schema = '".$db->dbname."' AND table_name = '".$this->table."'
        ");
        if(count($data) == 0){
            $db->query("
                CREATE TABLE `".$this->table."` (
                    ID varchar(100) primary key NOT NULL,
                    IP_ADDRESS varchar(100) NULL,
                    SESSION_DATA BLOB NULL,
                    CREATED_TIME double NULL,
                    LAST_USE double NULL
                )
                ENGINE=InnoDB
                DEFAULT CHARSET=utf8
                COLLATE=utf8_unicode_ci
            ");
            $db->query("CREATE INDEX `_amoeba_session_IP_ADDRESS_IDX` USING BTREE ON `_amoeba_session` (IP_ADDRESS)");
            $db->query("CREATE INDEX `_amoeba_session_CREATED_TIME_IDX` USING BTREE ON `_amoeba_session` (CREATED_TIME)");
            $db->query("CREATE INDEX `_amoeba_session_LAST_USE_IDX` USING BTREE ON `_amoeba_session` (LAST_USE)");
            $cache->save('__amoeba_session_table__',true,604800);
        }
        else{
            $cache->save('__amoeba_session_table__',true,604800);
        }
    }

    private function update_last_use(){
        $db = new Database();
        $time = time();

        $db->update($this->table,array(
            'LAST_USE' => $time
        ),"WHERE ID = :ID",['ID' => $this->sessid]);
    }

    private function delete_not_use(){
        $db = new Database();
        $date = new DateTime();

        $last_use = dateChange($date->format('Y-m-d H:i:s'),'-3 day',true);
        $last_use = strtotime($last_use);

        $db->delete($this->table,"WHERE LAST_USE <= :LAST_USE",array(
            'LAST_USE' => $last_use
        ));
    }
}

?>