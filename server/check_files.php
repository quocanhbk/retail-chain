<?php

    $username = "root";
    $password = "";
    $hostname = "localhost"; 
    $dbname = "bkrm_v2";
    $mysqli_bkrm_v2 = new mysqli("localhost",$username,$password,$dbname);
    $query = "SELECT id, image_url FROM default_items";
    $result = $mysqli_bkrm_v2->query($query);
    $result = $result->fetch_all();
    foreach($result as $record){
        if(!file_exists($record[1])){
            echo $record[0]."\t\t".$record[1];
            echo "<br>";
        }
    }
    echo "done";
?>