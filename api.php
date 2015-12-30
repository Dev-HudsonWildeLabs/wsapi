<?php
require_once('ws_data_service.php');
/*
 *  Example of ENV vars you need to setup:
    WSAPI_DSN="mysql:host=192.168.0.10;dbname=mydb;charset=utf8mb4";
    WSAPI_DBNAME="hope-not-root"; 
    WSAPI_DBPASSWORD="hope-not-password";    

 */
$callback=false;    
if(isset($_GET['callback']))
   $callback=$_GET['callback'];
if(!$callback){
    echo 'Please use JSONP format';
    return;
}
$conf['dsn']=getenv("WSAPI_DSN");
$conf['dbname']=getenv("WSAPI_DBNAME");
$conf['dbpassword']=getenv("WSAPI_DBPASSWORD");;

$ds=new WSDataService($conf['dsn'], $conf['dbname'], $conf['dbpassword'], array(PDO::ATTR_PERSISTENT => false));
$func='';
if(isset($_GET['func']))
    $func=$_GET['func'];
try{
    switch($func){
        case 'ping':
            echo 'pong';
            break;
        case 'load':
            if(isset($_GET['key']))
                $key=$_GET['key'];
            
            $length=0;
            $start=0;
            if(isset($_GET['length']))
                $length=$_GET['length'];
            if(isset($_GET['start']))
                $start=$_GET['start'];
            load($callback,$key,$start,$length);
            break;
        case 'create':
            if(isset($_GET['key']))
                $key=$_GET['key'];
            $eventTime=0;
            if(isset($_GET['time']))
                $eventTime=$_GET['time'];
            $json='';
            if(isset($_GET['json']))
                $json=$_GET['json'];
            create($key,$eventTime,$json);
            break;
        case 'delete':  
            if(isset($_GET['key']))
                $key=$_GET['key']; 
            if(isset($_GET['id']))
                $id=$_GET['id'];
            remove($key,$id);
            break;  
        case 'update':
            if(isset($_GET['key']))
                $key=$_GET['key'];
            if(isset($_GET['id']))
                $id=$_GET['id'];    
            $eventTime=0;
            if(isset($_GET['time']))
                $eventTime=$_GET['time'];
            $json='';
            if(isset($_GET['json']))
                $json=$_GET['json'];
            update($key,$id,$eventTime,$json);
            break;            

    }
}
catch(Exception $x){
    $s= jsonp(json_encode(array(
        "success" => false,
        "msg"=>"Unhandled Exception while executing your command ".$func.":".fe($x)
    )));
    echo $s;  
    return;
}
function jsonp($str){
    global $callback;
    //return '<script type="text/javascript">'.$callback."(".$str.")</script>";
return $callback."(".$str.")";

}
function load($callback,$key,$start,$length){
    global $ds;
    if(!$key){
        echo jsonp(json_encode(array(
            "success" => false,
            "msg"=>"User Key is missing"
        ))); 
        return;
    }
   
    $events_recs=$ds->load($key,$start,$length);
    $events=array();
    $i=0;
    foreach ($events_recs as $rec) {
        $events[$i]=$rec;
    }
    echo jsonp(json_encode(array(
        "success" => true,
        "events"=>$events
    ))); 
}
function create($key,$eventTime,$json){
    global $ds;
    if(!$key){
        echo jsonp(json_encode(array(
            "success" => false,
            "msg"=>"User Key is missing"
        ))); 
        return;
    } 
    $id=$ds->create($key,$eventTime,$json);
    if($id)
        echo jsonp(json_encode(array(
            "success" => true,
            "id"=>$id
        )));
    else{
       echo jsonp(json_encode(array(
        "success" => false,
        "msg"=>"Unable to create event"

        ))); 
    } 
}
function remove($key,$id){
    global $ds;
    if(!$key){
        echo jsonp(json_encode(array(
            "success" => false,
            "msg"=>"User Key is missing"
        ))); 
        return;
    }
    if(!$id){
        echo jsonp(json_encode(array(
            "success" => false,
            "msg"=>"id is missing"
        ))); 
        return;
    } 
   
    $ds->remove($key,$id);
    echo jsonp(json_encode(array(
        "success" => true
    ))); 
}
function update($key,$id,$eventTime,$json){
    global $ds;
    if(!$key){
        echo jsonp(json_encode(array(
            "success" => false,
            "msg"=>"User Key is missing"
        ))); 
        return;
    } 
    if(!$id){
        echo jsonp(json_encode(array(
            "success" => false,
            "msg"=>"id is missing"
        ))); 
        return;
    } 
    $ds->update($key,$id,$eventTime,$json);
    echo jsonp(json_encode(array(
        "success" => true
    )));
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