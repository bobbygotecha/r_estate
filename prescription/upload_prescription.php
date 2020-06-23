<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// include database and object files
include_once '../config/database.php';
include_once '../objects/prescription.php';
include_once '../objects/VerifyVisa.php';


// get database connection
$database = new Database();
$db = $database->getConnection();
//Variable Declartion
$result=array();
$result["data"]=array();


$accesstoken = $_POST["accesstoken"];
      $verifyvisa = new VerifyVisa($db);

      $final_status = $verifyvisa->verifyvisa($accesstoken);
      $response = $final_status["response"];
      $mobile = $final_status["mobile"];
      $customer_id = $final_status["customer_id"];
      if($response==false)
      {
        $result["response"] = "Failed";
        $result["msg"] = "Failed";
        echo json_encode($result);
        exit;
      }

$prescription_image = $_POST["prescription"];
$address = $_POST["address"];
$pincode = $_POST["pincode"];
$name = $_POST["name"];
$mobile = $_POST["mobile"];




// prepare product object
              $prescription = new Prescription($db);

              $stmt = $prescription->uploadPrescription($prescription_image,$customer_id,$address,$pincode,$name,$mobile);
              $stmt->store_result();
              if($stmt->affected_rows>0)
              {
                $result["response"] = "Success";
                $result["msg"] = "Success";
                echo json_encode($result);
              }
              else{
                $result["response"] = "Failed";
                $result["msg"] = "Failed";
                echo json_encode($result);
              }





?>
