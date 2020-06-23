<?php
include_once '../aws/vendor/autoload.php';
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
class Prescription{

    // database connection and table name
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

      public function uploadPrescription($prescription,$customer_id,$address,$pincode,$name,$mobile)
      {
        //upload image logic
        $prescription = str_replace(" ", "+", $prescription);
        $aaa = explode("base64,", $prescription);
        $aaaa = $aaa[1];
        $base64Decoded = base64_decode($aaaa);
        $x = $this->uploadImage($customer_id,$base64Decoded);

        //agent_fetch
        $stmta = $this->conn->prepare("select id from agent where status=1 order by rand()");
        $stmta->execute();
        $stmta->store_result();
        $stmta->bind_result($agent_id);
        $stmta->fetch();

        //shop_fetch

        $stmts = $this->conn->prepare("select id from shop where status=1 order by rand()");
        $stmts->execute();
        $stmts->store_result();
        $stmts->bind_result($shop_id);
        $stmts->fetch();


        //insert into prescription
        $status='PENDING';
        $stmti = $this->conn->prepare("INSERT INTO prescription(presc_data,customer_id,  presc_status, agent_id, shop_id) values (?,?,?,?,?)");
        $stmti->bind_param('sssss',$x,$customer_id,$status,$agent_id,$shop_id);
        $stmti->execute();
        $stmti->store_result();
        $presc_id = $stmti->insert_id;


        $stmt = $this->conn->prepare("INSERT INTO upload_prescription (name, address, presc_id, status,pincode,mobile,customer_id,shop_id,agent_id) values (?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssssssss',$name,$address,$presc_id,$status,$pincode,$mobile,$customer_id,$agent_id,$shop_id);
        $stmt->execute();
        return $stmt;
      }


    //s3 image upload
      public function uploadImage($id,$prescription)
      {
         $date = date('Ymdhis');
         $bucketName = 'medic-prescription';
         $IAM_KEY = 'AKIASDPVZ2MFX7L3YPEL';
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



      //display history

      public function history($customer_id)
      {
        $stmt = $this->conn->prepare("select up.id,up.amount, up.name, up.address, up.pincode, up.status, p.presc_data ,s.shop_name,s.shop_owner_mobile,s.shop_address,b.bill_data from prescription p JOIN upload_prescription up on p.id = up.presc_id JOIN shop s on p.shop_id = s.id LEFT JOIN bill b ON b.id=up.bill_id where p.customer_id = ? order by up.id desc ");
        $stmt->bind_param('s',$customer_id);
        $stmt->execute();
        return $stmt;
      }

      //home page history Prescription
      public function display_home_page_Prescription($customer_id)
      {
        $stmt = $this->conn->prepare("select up.id,up.amount, up.name, up.address, up.pincode, up.status, p.presc_data ,s.shop_name,s.shop_owner_mobile,s.shop_address from prescription p JOIN upload_prescription up on p.id = up.presc_id JOIN shop s on p.shop_id = s.id where p.customer_id = ? LIMIT 10");
        $stmt->bind_param('s',$customer_id);
        $stmt->execute();
        return $stmt;
      }

      public function imagedisplay($prescription)
              {
                  //$filename = 'bobby985.jpeg';
                  $bucketName = 'medic-prescription';
                  $IAM_KEY = 'AKIASDPVZ2MFX7L3YPEL';
                  $IAM_SECRET = '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';
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

                  $plain_url = $s3->getObjectUrl('medic-prescription', $prescription);
                  $plain_url . "\n";

                  $cmd = $s3->getCommand('GetObject', [
                      'Bucket' => 'medic-prescription',
                      'Key' => $prescription,
                  ]);
                  $signed_url = (string) $s3->createPresignedRequest($cmd, '+15 minute')->getUri();
                  return $signed_url;
              }
}
?>
