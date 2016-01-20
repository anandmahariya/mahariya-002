<?php
namespace Slim;

//Include config file
require_once './config.php';

class MongoDBLogWriter{
    protected $resource;
    protected $settings;
    private $db_connection;
    private $db;
    private $collection;
    
    
    public function __construct(){
        
        //Connect to database
        $this->db_connection = connect_db('local');
        $this->db = $this->db_connection->{'findit_log'};
        $this->collection = $this->db->{'intlayer'};
    }
    
    public function write($object, $level)
    {
        //Determine label
        $label = 'DEBUG';
        switch ($level) {
            case \Slim\Log::FATAL:
                $label = 'FATAL';
                break;
            case \Slim\Log::ERROR:
                $label = 'ERROR';
                break;
            case \Slim\Log::WARN:
                $label = 'WARN';
                break;
            case \Slim\Log::INFO:
                $label = 'INFO';
                break;
        }
        
        $data = array('key'=> $_SESSION['key'],'type'=>$label,'log'=>(string)$object,'created'=>new \MongoDate());
        $this->collection->insert($data);
    }
    
    function __destruct(){
        $this->db_connection->close();
    }
}