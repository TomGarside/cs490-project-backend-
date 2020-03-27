<?php 
// uncomment for debug
//ini_set('display_errors', 1); error_reporting(E_ALL);

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
                  echo $this->db->insertQuestion($request->name,
                                                 $request->description,
                                                 $request->difficulty,
                                                 $request->category,
                                                 $request->score, 
                                                 $request->testCases);
                  break;
            // return all questions  
            case 'get':
                  header('Content-Type: application/json');
                  echo $this->db->getAllQuestions();
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

     public function insertQuestion($name, $description, $difficulty, $category, $score, $testCases){
        $sql = "INSERT INTO questions (name, description, difficulty, category, score, testCases )";
        $sql.= " VALUES (\"" . $name . "\",\"" . $description . "\",\"" . $difficulty . "\",\""; 
        $sql.= $category  ."\",\"" . $score . "\",'" . json_encode($testCases) . "');";

        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        if($result = mysqli_query($connection,$sql)){
            $output["insert"] = true;
            http_response_code(201); 
        }else{
            http_response_code(400);
            $output["insert"] = false;
            $output["error"] = mysqli_error($connection);
        }
        return json_encode($output);
         
    } 
    public function getAllQuestions(){
        $sql = "SELECT * FROM questions;"; 
       
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        
        if($result = mysqli_query($connection,$sql)){         
            while ($row = $result->fetch_assoc()) {
                $testcases = $row["testCases"];
                unset($row["testCases"]);
                $row["testCases"]=json_decode($testcases);
                $results_array[] = $row;
            }
            $output = $results_array;
        }else{
            http_response_code(400);
            $output["error"]=$result; 
        }
        
        return json_encode($output);
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
