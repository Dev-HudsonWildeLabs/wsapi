<?php



Class WSDataService extends PDO {
 
    public static function exception_handler($exception) {
        // Output the exception details
        die('Uncaught exception: '. $exception->getMessage());
      
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

    public function load($key,$start,$length){
        $opt='';
        if($start)
            $opt=" and event_time>= FROM_UNIXTIME(".$start.")";
        if($length)
            $opt.=" and event_time< FROM_UNIXTIME(".($start+$length).")";
        $sth=$this->prepare("SELECT * from events where `key`=?".$opt);
        $sth->bindParam(1,$key,PDO::PARAM_STR);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);  
    }
    public function create($key,$eventTime,$json){
        $sth=$this->prepare("INSERT INTO events (`key`,event_time,json) VALUES(?,FROM_UNIXTIME(?),?)");
        $sth->bindParam(1,$key,PDO::PARAM_STR);
        $sth->bindParam(2,$eventTime,PDO::PARAM_STR);
        $sth->bindParam(3,$json,PDO::PARAM_STR);
        $sth->execute();
        return $this->lastInsertId();
    }
    public function remove($key,$id){
        $sth=$this->prepare("DELETE from events where `key`=? and id=?");
        $sth->bindParam(1,$key,PDO::PARAM_STR);
        $sth->bindParam(1,$id,PDO::PARAM_INT);
        $sth->execute();
    }
    public function update($key,$id,$eventTime,$json){
        $sth=$this->prepare("UPDATE events set event_time=FROM_UNIXTIME(?), json=? where `key`=? and id=?");
        $sth->bindParam(1,$eventTime,PDO::PARAM_STR);
        $sth->bindParam(2,$json,PDO::PARAM_STR);
        $sth->bindParam(3,$key,PDO::PARAM_STR);
        $sth->bindParam(4,$id,PDO::PARAM_INT);
        $sth->execute();
    }   
}
?>