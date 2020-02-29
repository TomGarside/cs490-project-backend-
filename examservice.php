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
                  echo $this->db->createExam($request->name, $request->creator, $request->questions);
                  break;
            // return all questions  
            case 'get':
                  header('Content-Type: application/json');
                  echo $this->db->getexam($_GET["name"]);

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

    public function createExam($name, $creator, $questions){
        $sql = "INSERT INTO exam (name,creator) VALUES (\"" . $name . "\",\"" . $creator . "\");"; 
        foreach($questions as &$question){
           $sql = $sql . "\nINSERT INTO examQuestion (examname, questionName) VALUES (\"" . $name . "\",\"" . $question->name . "\");";
        }
        
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        $result = mysqli_multi_query($connection,$sql);
        return mysqli_error($connection ) . $sql;
  
    }
    //validate password and return a string 
    public function getexam($examName){
        $sql = "SELECT * FROM exam WHERE name=\"" . $examName . "\" LIMIT 1;"; 
       
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        $result = mysqli_query($connection,$sql);
        $jsonResponse = $result->fetch_assoc();        
         
        $sqlQuestions = "SELECT * FROM questions INNER JOIN examQuestion ON questions.name=examQuestion.questionName WHERE examQuestion.examname=\"" . $examName ."\";";
        $questionResult = mysqli_query($connection,$sqlQuestions);

        while ($row = $questionResult->fetch_assoc()) {
            $results_array[] = $row;
           }
        return "{\"name\":\"" . $jsonResponse["name"] . "\", \"creator\":\"" . $jsonResponse["creator"] . "\", \"questions\":" . json_encode($results_array); 
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
