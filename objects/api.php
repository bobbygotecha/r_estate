<?php include_once '../aws/vendor/autoload.php';
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class API{
  private $conn;

  public function __construct($db){
      $this->conn = $db;
  }

   function savePropertyDetails($json_data){

    $data = json_decode($json_data,true);
    $name = $data['name'];
    $property_name = $data['property_name'];
    $mobile = $data['owner_mobile'];
    $address = $data['address'];
    $bathroom = $data['bathroom'];
    $room = $data['room'];
    $property_mode = $data['property_mode'];
    $property_type = $data['property_type'];
    $size = $data['size'];
    $city = $data['city'];
    $state = $data['state'];
    $year_built = $data['year_built'];
    $price = $data['price'];
    $description = $data['description'];
    $features = $data['features'];
    $image = $data['image'];




    $stmt = $this->conn->prepare("INSERT INTO property( mobile, owner_name, state, city) values (?,?,?,?)");
    $stmt->bind_param('ssss',$mobile,$name,$state,$city);
    $stmt->execute();
    $stmt->store_result();
    $x = $stmt->insert_id;
    $stmt->close();

    foreach ($features as $key => $value) {
      // code...
      $this->saveFeature($x,$value);
    }
    $this->InsertImage($x,$image);
    $this->propertyDetails($x,$property_name,$room,$bathroom,$price,$size,$property_type,$address,$description);
  }

  function InsertImage($id,$image){
    $xx = $this->uploadPropertyImage($id,$image);
    $stmt = $this->conn->prepare("INSERT INTO property_image( property_id, image, status) values (?,?,'ACTIVE')");
    $stmt->bind_param('ss',$id,$xx);
    $stmt->execute();
    $stmt->store_result();
    $stmt->close();
    return 1;
  }
  function propertyDetails($property_id,$name,$room,$bathroom,$price,$size,$property_type,$address,$description){
    $type_id = null;
    $stmt = $this->conn->prepare("INSERT INTO property_details( property_id, name, rooms, bathroom, price, size, type, type_id, address, description)
    values(?,?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param('ssssssssss',$property_id,$name,$room,$bathroom,$price,$size,$property_type,$type_id,$address,$description);
    $stmt->execute();
    $stmt->store_result();
    $stmt->close();
  }

  function saveFeature($property_id,$name){
    $status = 'ACTIVE';
    $stmt = $this->conn->prepare("INSERT INTO property_features( property_id, name, status) values (?,?,?)");
    $stmt->bind_param('sss',$property_id,$name,$status);
    $stmt->execute();
    $stmt->store_result();
    $stmt->close();
  }

  public function uploadPropertyImage($id,$prescription)
  {
     $date = date('Ymdhis');
     $bucketName = 'gautam-estate';
     $IAM_KEY = 'AKIAVMKUMA3EDSHZJVW7';
     $IAM_SECRET = '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';
   // Connect to AWS
   try {
       // You may need to change the region. It will say in the URL when the bucket is open
       // and on creation.
       $s3 = S3Client::factory(
           array(
               'credentials' => array(
                   'key' => $IAM_KEY,
                   'secret' => $IAM_SECRET,
               ),
               'version' => 'latest',
               'region' => 'ap-south-1',
           )
       );
   } catch (Exception $e) {

       die("Error: " . $e->getMessage());
   }
   $keyName = $filename1;

   // Add it to S3
   try {
       // Upload:

       $storeKey = $customer_id . rand(1, 100000);
       $s3->putObject(
           array(
               'Bucket' => $bucketName,
               'Key' => $storeKey . ".jpeg",
               'Body' => $prescription,
               'ContentEncoding' => 'base64',
               'ContentType' => 'image/jpeg',
               //'StorageClass' => 'REDUCED_REDUNDANCY'

           )
       );

       return $storeKey . ".jpeg";

   } catch (S3Exception $e) {
       die('Error:' . $e->getMessage());
   } catch (Exception $e) {
       die('Error:' . $e->getMessage());
   }

  }


}


?>
