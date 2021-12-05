<?php

use EasyVod\Facades\EasyVodFacade As Ev;

include "vendor\autoload.php";

/**
 *Author:Syskey
 *Date:2021/11/25
 *Time:12:23
 **/



Ev::init(["type"=>"weitang"]);

$parame = ["key"=>"复仇者联盟"];

$data = Ev::VodSearch($parame);

print_r($data);
