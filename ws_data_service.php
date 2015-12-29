<?php



Class WSDataService extends PDO {
 
    public static function exception_handler($exception) {
        // Output the exception details
        //die('Uncaught exception: '. $exception->getMessage());
        global $logService;
        $logService->log('ERROR','HUBDB ',fe($exception),'hubdb');  
       // die('Uncaught exception: '. $exception->getMessage());
    }
    
    public function __construct($dsn, $username='', $password='', $driver_options=array()) {

        // Temporarily change the PHP exception handler while we . . .
        set_exception_handler(array(__CLASS__, 'exception_handler'));

        // . . . create a PDO object
        parent::__construct($dsn, $username, $password, $driver_options);
        $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        // Change the exception handler back to whatever it was before
        restore_exception_handler();
    }
    public function loadDays($key,$opt,$id){
        $sql='';
        $s=false;
        if($opt=='pre'){
            $sql="SELECT * from days where ukey=? and id<? limit 7";
            $s=true;
        }
        else if($opt=='post'){
            $sql="SELECT * from days where ukey=? and id>? limit 7";
            $s=true;
        }
        else {
            $sql="SELECT * from days where ukey=? and date<=now() limit 7";
        }
        $sth=$this->prepare($sql);
        $sth->bindParam(1,$key,PDO::PARAM_STR);
        if($s)
            $sth->bindParam(1,$id,PDO::PARAM_STR);
        $sth->execute(); 
        $rs= $sth->fetchAll(PDO::FETCH_ASSOC);
        return $rs;

    }
   
}
?>