<?php

require_once dirname(__FILE__) . '/../videos/configuration.php';
require_once dirname(__FILE__) .'/Streamer.php';
class Login {

    static function run($user, $pass, $youPHPTubeURL) {
        global $global;
        if (substr($youPHPTubeURL, -1) !== '/') {
            $youPHPTubeURL .= "/";
        }
        
        $postdata = http_build_query(
                array(
                    'user' => $user,
                    'pass' => $pass
                )
        );

        $opts = array(
            "ssl" => array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            ),
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );

        $context = stream_context_create($opts);

        $result = @file_get_contents($youPHPTubeURL . 'login', false, $context);
        if(empty($result)){
            $object = new stdClass();
            $object->streamer = false;
            $object->streamers_id = 0;
            $object->isLogged = false;
            $object->isAdmin = false;
            $object->canUpload = false;
            $object->canComment = false;
        }else{        
            $object = json_decode($result);
            $object->streamer = $youPHPTubeURL;
            $object->streamers_id = 0; 
            if(!empty($object->canUpload)){
                $object->streamers_id = Streamer::createIfNotExists($user, $pass, $youPHPTubeURL);
            }
            if($object->streamers_id){
                $s = new Streamer($object->streamers_id);
                $object->isAdmin = $s->getIsAdmin();
            }
        }
        $_SESSION['login'] = $object;   
        
    }

    static function logoff() {
        unset($_SESSION['login']);
    }

    static function isLogged() {
        return !empty($_SESSION['login']->isLogged);
    }

    static function isAdmin() {
        return !empty($_SESSION['login']->isAdmin);
    }

    static function canUpload() {
        return !empty($_SESSION['login']->canUpload);
    }

    static function canComment() {
        return !empty($_SESSION['login']->canComment);
    }
        
    static function getStreamerURL() {
        if(!static::isLogged()){
            return false;
        }
        return $_SESSION['login']->streamer;
    }
    
    static function getStreamerId() {
        if(!static::isLogged()){
            return false;
        }
        return $_SESSION['login']->streamers_id;
    }

}