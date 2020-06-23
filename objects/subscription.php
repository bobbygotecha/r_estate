<?php
class Subscription{

    // database connection and table name
    private $conn;

    public function __construct($db){
        $this->conn = $db;
    }

      public function count_sub($category_id,$sub_category_id)
      {
        $stmt = $this->conn->prepare("select count(1) as count  from subscription where category_id='$category_id' and sub_category_id='$sub_category_id'");
        $stmt->execute();
        return $stmt;
      }
      public function final_sub($category_id,$sub_category_id)
      {
        $stmt = $this->conn->prepare("select subscription,amount,description,title,subscription_plan,subscription_type from subscription where category_id='$category_id' and sub_category_id='$sub_category_id'");
        $stmt->execute();
        return $stmt;
      }
}
?>
