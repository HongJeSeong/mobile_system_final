<?php
$str = file_get_contents('./data.json');
$json = json_decode($str, true);
$state= $json["name"];
$color = $json["color"];
$servername = "localhost";
$username = "root";
$password = "hong";
$database = "office_state";

$conn = new mysqli($servername, $username, $password, $database);
 
$sql = "SELECT count, number FROM state order by time DESC limit 1";
$result = $conn->query($sql);
$count=0;
$number="";
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
	$count=$row['count'];
	$number=$row['number'];
    }
} else {
    echo "0 results";
}
$conn->close();
if($color=="red"){
$font="#ff0505";

}else if($color=="green"){
$font="#40d808";

}else if($color=="blue"){
$font="#000d9c";

}else{
$font="#ffdc18";
}

?>

<div style="text-align:center;"> 
<h1 style="background-color:#0bad6e;">Office State</h1>

	<div style="background-color:whitesmoke">
	<h2 style="font-size:45px"><font style="color:#1e58bd;"><?php echo $number;?></font> Office</h2>
	<div style="border: solid #068ccb;font-size: 30px;margin: 10px 30%;"> Current : <?php if($state=="Absence")$count=0; echo $count;?></div>
	<?php

	echo "<h2 style='font-size:45px;color:".$font."'>".$state." State</h2>"; 
		?>
	</div>	
</div>
<div style="background-color: aliceblue;padding: 250px 0 250px 0;"></div>
