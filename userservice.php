<?php 
// uncomment for debug
// ini_set('display_errors', 1); error_reporting(E_ALL);

// handles http connection and initializes DB object 
class httpHandler {

    // load DB creds from file and initialize DB object 
    public function __construct($fileName){
        // load db credentials from protected secrets file 
  	$secretsFile = fopen($fileName,"r") or die ("Unable to open file! " . $fileName);

        $servername = trim(fgets($secretsFile)); 
        $username = trim(fgets($secretsFile)); 
        $password = trim(fgets($secretsFile));

        fclose($secretsFile);   
      
        $this->db = new dbConnect($servername, $username, $password); 
    } 

    // handles web requests 
    public function handleRequest($request, $method, $body){

        switch($method){
 
            case 'post':
                  $request = json_decode($body);
                  header('Content-Type: application/json');
                  echo $this->db->validateUser($request->user,$request->password);
                  break;

            default:
                  http_response_code(405);
        }
   }
}
// class handles db connection   
 class dbConnect {
     // initialize class
     public function __construct($servername, $username, $password){
         $this->servername = $servername; 
         $this->username = $username;
         $this->password = $password; 
         $this->dataBase = $username;         
                                                  
         // create table if it does not exist           
         $sql = "CREATE TABLE IF NOT EXISTS user (name VARCHAR(20), password VARCHAR(100));";  
         $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
         $result = mysqli_query($connection,$sql); 
          
         if(!$result){ die("failed to create table"); }
     }
    //validate password and return a string 
    public function validateUser($user,$password){
        $sql = "SELECT password, role FROM user WHERE name = '" . $user ."' LIMIT 1"; 
       
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        $result = mysqli_query($connection,$sql);
        $data  = mysqli_fetch_object($result);
       
        $retVal = "false";
        $retRole = "";
        if(password_verify($password,$data->password)){
           $retVal = "true";
           $retRole = $data->role; 
        }
        return "{\"ValidUser\":\"" . $retVal. "\",\"role\":\"" . $retRole . "\"}";    
      }
}
// initialize http object 
$http = new httpHandler("../secrets.txt"); 

// take variables from request  
$request = explode('/',$_SERVER['PATH_INFO']);
$method = strtolower($_SERVER['REQUEST_METHOD']);
$body = file_get_contents('php://input');

// handle request 
$http->handleRequest($request, $method, $body);

?>