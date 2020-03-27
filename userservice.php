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
            case 'get':
                  header('Content-Type: application/json');
                  if ($_GET["role"]){
                      echo $this->db->getAllRole($_GET["role"]); 
                  }else{
                      http_response_code(400);
                  }
                  break;
            case 'post':
                  $body = json_decode($body);
                  header('Content-Type: application/json');
                  echo $this->db->validateUser($body->user,$body->password);
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
     }
     // get all users with a specified role 
     public function getAllRole($role){
         $sql = "SELECT name FROM user WHERE role = \"" . $role . "\";";
         $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
         if($data  = (mysqli_query($connection,$sql))){
             while ($row = $data->fetch_assoc()) {
                 $results_array[] = $row;
             }
             $returnVal[$role] = $results_array;
             }else{
             http_response_code(400);
             $returnVal = "{\"error\":\"" . mysqli_error($connection) . "\"}";
             }
             
         return json_encode($returnVal); 
     }

    //validate password and return a string 
     public function validateUser($user,$password){
        $sql = "SELECT password, role FROM user WHERE name = '" . $user ."' LIMIT 1"; 
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        $data  = mysqli_fetch_object(mysqli_query($connection,$sql));
        $retRole = "none";
        if(password_verify($password,$data->password)){
            $retVal = "true";
            $retRole = $data->role; 
        }else{
            http_response_code(403); 
            $retVal = "false";
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
