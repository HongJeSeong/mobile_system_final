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
$number="";
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
	$current=$row['count'];
	$number=$row['number'];
    }
} else {
    echo "0 results";
}

?>
<script type="text/javascript" src="https://code.jquery.com/jquery-1.12.4.min.js"></script>

<div style="text-align:center;"> 
<h1 style="background-color:#0bad6e;">Office State Control</h1>

	<div style="background-color:whitesmoke">
	<h2 style="font-size:45px"><font style="color:#1e58bd;"><?php echo $number;?></font> Office</h2>

	<input style="font-size:40px;"type="button" id="button1" onclick="button1_click();" value="Presence" />
	<input style="font-size:40px;" type="button" id="button2" onclick="button2_click();" value="Absence" />
	<input style="font-size:40px;"  type="button" id="button3" onclick="button3_click();" value="School Work" />
	<input style="font-size:40px;"  type="button" id="button4" onclick="button4_click();" value="wait" />
	<div style="border: solid #068ccb;font-size: 30px;margin: 10px 30%;">Current : <?php if($state=="Absence")$current=0;echo $current;?></div>
	</div>
	<div style="background-color:whitesmoke">
	<h2>Log Section</h2>
	<?php 
	$sql = "SELECT count, time  FROM state order by time DESC limit 20";
	$result = $conn->query($sql);
	if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
	echo "<div style='font-size:19px;'>[TIME : ".$row["time"]."] COUNT : ".$row["count"]."</div>";
    }
	
}
	$conn->close();
	?>
	</div>
	<div id="cam">
	<h2>Camera Section</h2>
	<embed src="http://192.168.1.29:8080/stream_simple.html"style="width:100%;height:100%;"/>
	</div>	
</div>
<script>
var state = new Object();
var value;
function button1_click() {
        alert("Presence");
        state.name="Presence";
        state.color="green";
        value = JSON.stringify(state);
        ajaxSend(value);
}

function button2_click() {
        alert("Absence");
        state.name="Absence";
        state.color="red";
        value = JSON.stringify(state);
        ajaxSend(value);
}
function button3_click() {
        alert("School Work");
        state.name="School Work";
        state.color="yellow";
        value = JSON.stringify(state);
        ajaxSend(value);
}

function button4_click() {
        alert("wait");
        state.name="wait";
        state.color="blue";
        value = JSON.stringify(state);
        ajaxSend(value);
}


function ajaxSend(state_value){
        $.ajax({ url : "file.php",
                 data : { val : state_value },
                 type : "POST",
                 dataType : "json",
                 success : function(result) {
                         if(result.success == false) {
                                 alert(result.msg);
                                         return; }
                                 alert(result.data); } });

}

</script>

