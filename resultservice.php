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
            // insert result  
            case 'post':
                  $body = json_decode($body);
                  header('Content-Type: application/json');
                  echo $this->db->createExamResult($body->user, $body->exam, $body->results);
                  break;
            // updates result with professor updates  
            case 'put':
                  $body = json_decode($body);
                  header('Content-Type: application/json');
                  echo $this->db->updateExamResult($body->user, $body->exam, $body->question, $body->autograde, $body->adjustedGrade);
                  break;
            // return result (combo of student and exam) if student not specified gets all results for exam   
            case 'get':
                  header('Content-Type: application/json');
                  if (isset($_GET["user"])){
                      echo $this->db->getExamResult($_GET["user"], $_GET["exam"]);
                  } else{
                      echo $this->db->getAllExamResults( $_GET["exam"]);
                  }
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
     //post exam results for a single student  
    public function createExamResult($name, $exam, $results){
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase); 
        $sql=""; 
        foreach($results as &$result){
            $sql.= "\nINSERT INTO"; 
            $sql.= " questionResult (user, exam, question, answer, autograde, adjustedGrade, finalGrade)";
            $sql.= " VALUES (\"" . $name . "\",\"" . "$exam" . "\",\"" . $result->question . "\",\"" . mysqli_real_escape_string($connection,$result->answer) . "\",\"" . $result->autograde . "\",\"" . $result->adjustedGrade ."\",\"" . $result->finalGrade ."\");";    
        }
        
        $result = mysqli_multi_query($connection,$sql);
        return mysqli_error($connection );
  
    }
     // get all student results for a single exam
     public function getAllExamResults($exam){
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase); 
        $sql = "SELECT user.name AS user, questions.name as question, questions.testCases, answer, autoGrade, adjustedGrade, finalGrade  FROM questionResult JOIN exam ON questionResult.exam=exam.name JOIN questions ON questionResult.question=questions.name JOIN user ON questionResult.User=user.name WHERE questionResult.exam = \"" . $exam . "\";"; 
        $result = mysqli_query($connection,$sql);
        while ($row = $result->fetch_assoc()) {
           $row["testCases"] = json_decode($row["testCases"]); 
           $results_array[] = $row;
        }
        $output[$exam]=$results_array;
        return json_encode($output); 
     }
    // get exam results for a sigle student 
    public function getExamResult($user, $examName){
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        $sql = "SELECT  questions.name as question, questions.testCases, answer, autoGrade, adjustedGrade, finalGrade  FROM questionResult JOIN exam ON questionResult.exam=exam.name JOIN questions ON questionResult.question=questions.name JOIN user ON questionResult.User=user.name WHERE questionResult.exam = \"" . $examName . "\" AND questionResult.user = \"". $user. "\";";
        $result = mysqli_query($connection,$sql); 
        while ($row = $result->fetch_assoc()) {
            $row["testCases"] = json_decode($row["testCases"]);
            $results_array[] = $row;
        }
        $output["user"] = $user; 
        $output["exam"] = $examName; 
        $output["results"] = $results_array;
        return json_encode($output);
     }
    //update exam result for student 
     public function updateExamResult($user, $exam, $question, $autoGrade, $adjustedGrade) {
        $connection = mysqli_connect($this->servername, $this->username, $this->password, $this->dataBase);
        $sql = "UPDATE questionResult SET autoGrade=\"". $autoGrade . "\", adjustedGrade=\"" . $adjustedGrade . "\" WHERE User=\"" . $user . "\" AND exam=\"" . $exam . "\" AND question=\"" . $question . "\";";
        $result = mysqli_query($connection, $sql);
        return $result; 
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
