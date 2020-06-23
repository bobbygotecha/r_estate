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
$result["history_data"]=array();

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


// prepare product object
              $prescription = new Prescription($db);

              $stmt = $prescription->history($customer_id);
              $stmt->store_result();
              $stmt->bind_result($id,$amount,$name,$address,$pincode,$status,$prescription_file,$shop_name,$shop_mobile,$shop_address,$bill_data);

              if($stmt->affected_rows>0)
              {
                while($stmt->fetch())
                {

                  $prescription_temp = new Prescription($db);
                  $prescription = $prescription_temp->imagedisplay($prescription_file);

                  $data_item = array(
                    "id" => $id,
                    "amount" => $amount,
                    "name" => $name,
                    "address" => $description,
                    "pincode" => $pincode,
                    "status" => $status,
                    "prescription" => $prescription,
                    "shop_name" => $shop_name,
                    "shop_address" => $shop_address,
                    "shop_mobile" => $shop_mobile,
                    "bill_data"=>$bill_data

                  );
                  array_push($result["history_data"],$data_item);
                }
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
