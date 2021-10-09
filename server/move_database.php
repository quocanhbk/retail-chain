<?php
    $username = "root";
    $password = "";
    $hostname = "localhost"; 
    $dbname = "barcode_data";
    $mysqli_barcode = new mysqli("localhost",$username,$password,$dbname);
    
    $dbname = "bkrm_v2";
    $mysqli_bkrm_v2 = new mysqli("localhost",$username,$password,$dbname);
    $query = "DELETE FROM `default_items` WHERE 1";
    
    $success_insert_flag = 1;
    if($mysqli_bkrm_v2->query($query)){
        $query = "SELECT name FROM categories";
        $barcode_result = $mysqli_barcode->query($query);
        $barcode_result = $barcode_result->fetch_all();
    
        //delete category in default_categories
        $query = "DELETE FROM `default_categories` WHERE 1";
        if($mysqli_bkrm_v2->query($query)){
            $category_flag = 1;
            $query = "ALTER TABLE `default_categories` AUTO_INCREMENT = 1";
            echo "Altering AUTO_INCREMENT of default_categories table <br>";
            if($mysqli_bkrm_v2->query($query)){
                //insert category to default_categories
                foreach($barcode_result as $row){
                    $query = "INSERT INTO `default_categories`(`name`) VALUES ('$row[0]')";
                    if($mysqli_bkrm_v2->query($query)){
                        echo "Insert category: ".$row[0];
                        echo "<br>";
                    } else {
                        $category_flag = 0;
                        echo $mysqli_bkrm_v2->error;
                        break;
                    }
                }
            } else {
                $category_flag = 0;
                echo $mysqli_bkrm_v2->error;
            }
    
            if($category_flag){
                $query = "SELECT category_id, product_name, bar_code, image_url,id  FROM barcode_data";
                $barcode_result = $mysqli_barcode->query($query);
                $barcode_result = $barcode_result->fetch_all();
    
                //delete all files in upload/default
                $path = getcwd()."\upload\default\*";
                // echo $path;
                $files = glob($path); // get all file names
                foreach($files as $file){ // iterate files
                    if(is_file($file)) {
                        unlink($file); // delete file
                    }
                }
    
                //delete all in default_items
                $query = "DELETE FROM `default_items` WHERE 1";
                if($mysqli_bkrm_v2->query($query)){
                    $query = "ALTER TABLE `default_items` AUTO_INCREMENT = 1";
                    echo "Altering AUTO_INCREMENT of default_categories table <br>";
                    if($mysqli_bkrm_v2->query($query)){
                        echo "Inserting data to default_items...<br>";
                        foreach($barcode_result as $row){
                            $img_name = substr($row[3],7);
                            $img_url = "upload/default/".$img_name;
                            $name = str_replace("'", "\'", $row[1]);
                            $query = "INSERT INTO `default_items`(`category_id`, `name`, `bar_code`, `image_url`) VALUES ($row[0], '$name', '$row[2]', '$img_url')";
                            if($mysqli_bkrm_v2->query($query)){
                                // echo "Insert items: ".$row[4];
                                // echo "<br>";
                                copy($row[3],$img_url);
                            } else {
                                $success_insert_flag = 0;
                                echo $mysqli_bkrm_v2->error;
                                break;
                            }
                        }
                    } else {
                        echo $mysqli_bkrm_v2->error;
                    }
                } else {
                    echo $mysqli_bkrm_v2->error;
                }
            }
        } else {
            echo $mysqli_bkrm_v2->error;
        }
    } else {
        $success_insert_flag = 0;
        echo $mysqli_bkrm_v2->error;
    }

    $final_msg = $success_insert_flag? "Success":"Fail";
    echo $final_msg;
    
    $mysqli_barcode->close();
    $mysqli_bkrm_v2->close();
?>