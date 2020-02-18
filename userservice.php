<?php 
// uncomment for debug 
//ini_set('display_errors', 1); error_reporting(E_ALL);


class httpHandler {

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
    
    case 'get':
          header('Content-type: text/plain');
          $resp = $this->db->getHash(htmlspecialchars($_GET["password"])); 
          echo $resp;
          break;
 
    case 'post':
          $request = json_decode($body);
          header('Content-type: application/json');
          echo "{\"ValidUser\":\"" . $this->db->validateUser($request->user,$request->password) . "\"}";
          break;

    default:
          http_response_code(405);

    }
    

   }

}



 class dbConnect {
    
     public function __construct($servername, $username, $password){
          $this->servername = $servername; 
          $this->username = $username;
          $this->password = $password; 
          $this->dataBase = $username;                                                           
          // create table if it does not exist 
          
          $sql = "CREATE TABLE IF NOT EXISTS Users (name VARCHAR(20), password VARCHAR(100));";  
          $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
          $result = mysqli_query($connection,$sql); 
          
          if(!$result){
	      die("failed to create table");  
          }


     }

     public function getHash($password){

         $retVal = password_hash($password,PASSWORD_DEFAULT);
         return $retVal; 
     }

     public function validateUser($user,$password){
        $sql = "SELECT password FROM Users WHERE name = '" . $user ."' LIMIT 1"; 
       
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        $result = mysqli_query($connection,$sql);
        $storedPassword = mysqli_fetch_object($result);
       
        $retVal = "false";
        
        if(password_verify($password,$storedPassword->password)){
           $retVal = "true"; 
        }

        return $retVal;    
      }
     
}

$http = new httpHandler("../secrets.txt"); 

$request = explode('/',$_SERVER['PATH_INFO']);

$method = strtolower($_SERVER['REQUEST_METHOD']);

$body = file_get_contents('php://input');

$http->handleRequest($request, $method, $body);


?>