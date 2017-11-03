<?php

//variable
$dbservername = "OM-DB-10";
$dbusername = "bkp";
$dbuserpass = "backup";
$dbname = "backup";

//Create connection
try {
	$conn = new PDO("mysql:host=$dbservername;dbname=$dbname", $dbusername, $dbuserpass);
	// set the PDO error mode to exception
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//    echo "Connected successfully"; 
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }

// Check connection
/*if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 
echo "Connected successfully";*/
?>