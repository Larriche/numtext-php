<?php
require('numtext.php');

$converter = new Numtext();
$input = "";

if(isset($_POST['convert'])){
	$input = $_POST['original'];
	$output =  $converter->convert($input);
	
}

?>

<!DOCTYPE html>
<html>
 <head>
   <title>Numtext PHP test</title>
   <meta charset="utf-8">
   <style>
     input[type="text"]{
     	width: 600px;
     }
   </style>
 </head>
 <body>
   <form method="POST" action="test.php">
     <p>
       <label>Original:</label>
       <input type="text" name="original" value="<?php echo $input;?>">
     </p>

     <p>
        <input type="submit" value="Convert" name="convert">
     </p>
   </form>

   <?php
     if(isset($output)){
     	echo $output;
     }
   ?>
 </body>
 </html>