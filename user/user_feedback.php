<?php
	require_once "../dbconn.php";



		function ratingSelect($name) {
	    echo "<select name='$name' required><option value=''> Select your Satisfaction </option>";
	    for ($i = 5; $i >= 1; $i--) echo "<option value='$i'>$i</option>";
	    echo "</select>";
		}


	function satisfactionRadio($name) {
	    $options = [5 => 'Very Satisfied', 4 => 'Satisfied', 3 => 'Neutral',
	                2 => 'Dissatisfied', 1 => 'Very Dissatisfied'];
	    foreach ($options as $value => $label) {
	        echo "<label><input type='radio' name='$name' value='$value' required> $label</label><br>";
	    }
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<h1>Citizen Feedback</h1>

	<form method="post">
		<h2>Satisfaction Ratings</h2>
  			<p>Overall satisfaction (1 = poor, 5 = excellent):
  		<br>
  			<?php ratingSelect('satisfaction_rating'); ?></p>
  		<hr>

  		<h2>Feedback Comments</h2>
  		<p><textarea name="comments" rows="4" cols="50"></textarea></p>

  		<hr>


  	<h2>Service Evaluation</h2>
	  	<p>How satisfied are you with the timeliness of the service?<br><?php satisfactionRadio(''); ?></p>
	  	<p>How satisfied are you with the professionalism of the staff?<br><?php satisfactionRadio(''); ?></p>
	  	<p>How satisfied are you with the communication and updates?<br><?php satisfactionRadio(''); ?></p>
 

 <hr>
	  	<h2>Complaint Resolution Feedback</h2>
		  <p>Was your complaint resolved?<br>
			    <select name="resolution_status">
			    <option value=" " disabled selected></option>
			    <option value="yes">Yes</option>
			    <option value="no">No</option>
			    </select>
		  </p>

	  	<p>Comments on the resolution:<br><textarea name="resolution_comment" rows="4" cols="50"></textarea></p>
	 
	  	<p><input type="submit" value="Submit Feedback"></p>
		<hr>
	</form>
</body>
</html>
