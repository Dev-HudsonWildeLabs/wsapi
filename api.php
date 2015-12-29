<?php
require_once('ws_data_service.php');
/*
 *  Example of ENV vars you need to setup:
    WSAPI_DSN="mysql:host=192.168.0.10;dbname=mydb;charset=utf8mb4";
    WSAPI_DBNAME="hope-not-root"; 
    WSAPI_DBPASSWORD="hope-not-password";    

 */
$conf['dsn']=getenv("WSAPI_DSN");
$conf['dbname']=getenv("WSAPI_DBNAME");
$conf['dbpassword']=getenv("WSAPI_DBPASSWORD");;

$ds=new WSDataService($conf['dsn'], $conf['dbname'], $conf['dbpassword'], array(PDO::ATTR_PERSISTENT => false));
$func='';
if(isset($_GET['func']))
    $task=$_GET['func'];
try{
    switch($task){
        case 'loadDays':
        if(isset($_GET['opt']))
            $opt=$_GET['opt'];
        if(isset($_GET['id']))
            $id=$_GET['id'];
        if(isset($_GET['key']))
            $id=$_GET['key'];
        loadDays($opt,$id);
        break;
    }
}
catch(Exception $x){
    echo json_encode(array(
        "success" => false,
        "msg"=>"Unhandled Exception while executing your command ".$func.":".fe($x);
    )); 
    return;
}
function loadDays($key,$opt,$id){
    global $ds;
    if(!$key){
        echo json_encode(array(
            "success" => false,
            "msg"=>"User Key is missing"
        )); 
        return;
    }
    if($opt&&!$id){
        echo json_encode(array(
            "success" => false,
            "msg"=>"argument 'id' has to be provided if 'opt' is not empty"
        )); 
        return;
    }
    $days-$ds->loadDays($key,$opt,$id);
    echo json_encode(array(
        "success" => true,
        "days"=>$days
    )); 
}
function loadEvents($key,$id){
    global $ds;
    if(!$key){
        echo json_encode(array(
            "success" => false,
            "msg"=>"User Key is missing"
        )); 
        return;
    }
    if($id){
        echo json_encode(array(
            "success" => false,
            "msg"=>"id is missing"
        )); 
        return;
    }
    $events-$ds->loadEvents($key,$id);
    echo json_encode(array(
        "success" => true,
        "events"=>$events
    )); 
}
function fe($x){ //formatException
    $trace=$x->getTrace();
    $file=$x->getFile();
    $line=$x->getLine();
   // $mes=$x->getMessage();
    $mes = $x->getMessage();
    $mes .= '" @ ';
    if($trace[0]['class'] != '') {
      $mes .= $trace[0]['class'];
      $mes .= '->';
    }
    $mes .= $trace[0]['function'];

    return 'File: '.$file.' Line: '.$line.' Message:'. $mes;
}