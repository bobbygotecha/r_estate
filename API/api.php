<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

// include database and object files
include_once '../config/database.php';
include_once '../objects/api.php';

// get database connection
$database = new Database();
$db = $database->getConnection();
            $api = new API($db);

              $response = $api->savePropertyDetails($data);
              $result=array();
              $result["data"]=array();

                $result["response"] = "Success";
                $result["msg"] = "Success";
                echo json_encode($result);


?>
