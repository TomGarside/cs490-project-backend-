<?php 
// uncomment for debug
ini_set('display_errors', 1); error_reporting(E_ALL);

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
            // insert test case 
            case 'post':
                  $request = json_decode($body);
                  header('Content-Type: application/json');
                  echo $this->db->insertQuestion($request->name,$request->testResults);
                  break;
            // return all questions  
            case 'get':
                  header('Content-Type: application/json');
                  echo $this->db->getAllQuestions();

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
         $sql = "CREATE TABLE IF NOT EXISTS questions (name VARCHAR(100), tests VARCHAR(1000));";  
         $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
         $result = mysqli_query($connection,$sql); 
          
         if(!$result){ die("failed to create table"); }
     }

    public function insertQuestion($name, $testResults){
        $sql = "INSERT INTO questions (name, tests) VALUES (\"" . $name . "\",\"" . $testResults . "\");";
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        $result = mysqli_query($connection,$sql);
        return $sql;
  
    }
    //validate password and return a string 
    public function getAllQuestions(){
        $sql = "SELECT * FROM questions;"; 
       
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        $result = mysqli_query($connection,$sql);
        
        while ($row = $result->fetch_assoc()) {
           $results_array[] = $row;
        }
        return json_encode($results_array); 
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