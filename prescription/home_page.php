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
include_once '../objects/banner.php';

// get database connection
$database = new Database();
$db = $database->getConnection();
//Variable Declartion
$result=array();
$result["data"]=array();
$result["banner_data"]=array();



 $accesstoken = $_POST["accesstoken"];
// prepare  object
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
	      
	      $banner = new Banner($db);

              $stmt_banner = $banner->banner();
              $stmt_banner->store_result();
              $stmt_banner->bind_result($id,$path,$status,$priority,$callback_key,$callback_type,$title);


              $prescription = new Prescription($db);

              $stmt = $prescription->display_home_page_Prescription($customer_id);
              $stmt->store_result();
              $stmt->bind_result($id,$amount,$name,$address,$pincode,$status,$prescription_file,$shop_name,$shop_mobile,$shop_address);

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

                  );
                  array_push($result["data"],$data_item);
                }
                while($stmt_banner->fetch())
                {

                  $data_item1 = array(
                    "id" => $id,
                    "path" => $path,
                    "priority" => $priority,
                    "callback_key" => $callback_key,
                    "callback_type" => $callback_type,
                    "title" => $title,
                  );
                  array_push($result["banner_data"],$data_item1);
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
