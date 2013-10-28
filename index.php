<?php /* FILEVERSION: v1.0.1b */ ?>
<?php

require_once('settings.inc.php');

class imageLoader {
	public $img;
	public $pupil;
	public $pupilFriendly;
	public $mysqli;

	//corrdinates
	public $lx;
	public $ly;
	public $rx;
	public $ry;

	//image information
	public $id;
	public $name;
	public $newImage;

	//string variables
	public $button;
	public $instruction;
	public $warning;
	
	
	public function imageLoader(){

		if(!isset($_GET['img'])){
			//if there is no get and no image return the image sselection list
			echo $this->selectImage();
		} else {
			//database connection
			$this->mysqli = new mysqli(_DB_SERVER_,_DB_USER_,_DB_PASSWD_,_DB_NAME_);
			if (!$this->mysqli){die("Could not connect to MySQLi: " . mysql_error());}

			//if there is get information then set the image
			$this->img = $_GET['img'];

			//removing file extention
			$v = array('.jpg','.jpeg','.png','.gif');
			$this->name = str_replace($v,'', strtolower($_GET['img']));

			//setting coordinate properties
			$this->setCoordinateProperties();

			//setting the pupil that is being worked on
			$this->pupilSelect();

			//check if an update has been submitted 
			$this->submissionCheck();
			
			//checking if image has been created
			$this->checkForExistingImage();

		}
	}
	public function submissionCheck(){

		//checking for submission of left eye coordinates
		if(isset($_POST["form_x"])){
			$this->updateCoordinates($_POST['pupil']);
			echo '<script>location.reload();</script>';
		}

		//checking for delete 
		if(isset($_POST['deleteimagevalues'])){
			$this->deleteCoordinates();
			echo '<script>location.reload();</script>';
		}

		//checking for crop 
		if(isset($_POST['crop'])){
			$this->cropPhoto();
			echo '<script>location.reload();</script>';
		}

		//checking for resize 
		if(isset($_POST['resize'])){
			$this->resizePhoto();
		}
	}
	public function selectImage(){
		//return array
		$a = array();

		//image directory
		$dirs = scandir('images/');

		//files to ignor
		$noShow = array(".","..",'resized');

		array_push($a,'<p>Please select an image</p><ul>');

		//for each image display a link with their name
		foreach($dirs AS $file){
			if(!is_dir($file) && (!in_array($file,$noShow))){
				array_push($a,"<li><a href=\"index.php?img=" . $file . "\">" . strtoupper($file) . "</a></li>");
			}
		}

		//closing unordered list
		array_push($a,'</ul>');

		//returning values
		return implode('',$a);
	}
	public function checkForExistingImage(){
		//setting filename
		$file = 'images/resized/' . $this->name . '_resized.png';

		//checking if file is present
		if(file_exists($file)){

			//making sure new image variable is set
			$this->newImage = $this->name . '_resized.png';

			//showing image
			echo $this->displayImage();

		} else {
			
			//display pupil configuration
			echo $this->pupilForm();
		}
	}
	public function setCoordinateProperties(){

		$sql = $this->mysqli->prepare('SELECT `id`,`name`,`lx`,`ly`,`rx`,`ry` FROM `' . _DB_NAME_ . '`.`imageworkshop` WHERE `name` = ?');
		$sql->bind_param('s', $this->name); 
		$sql->execute(); 
		$sql->store_result();
		$sql->bind_result($id, $name, $lx, $ly, $rx, $ry); // get variables from result.
		$sql->fetch();

		
		if($sql->affected_rows < 1){
			//checking if there is a record for the selected image and if not inserting it
			$this->addImageRecord();
		}
		
		//setting the object properties unless the value is null then setting to 0
		if(!is_null($lx)){
			$this->lx = $lx;
		} else {
			$this->lx = 0;
		}

		if(!is_null($ly)){
			$this->ly = $ly;
		} else {
			$this->ly = 0;
		}

		if(!is_null($rx)){
			$this->rx = $rx;
		} else {
			$this->rx = 0;
		}

		if(!is_null($ry)){
			$this->ry = $ry;
		} else {
			$this->ry = 0;
		}

		if(!is_null($id)){
			$this->id = $id;
		} else {
			$this->id = 0;
		}

		if(!is_null($name)){
			$this->name = $name;
		} else {
			$this->name = 0;
		}
	}
	public function addImageRecord(){

		

		//inserting new value
		$sql = 'INSERT INTO `' . _DB_NAME_ . '`.`imageworkshop` (`name`) VALUES (\'' . $this->name . '\')';
		$sql = $this->mysqli->prepare($sql);
		$sql->execute(); 
	}
	public function updateCoordinates(){
		
		//checking if value being submitted is correct type
		if(in_array($this->pupil, array('L','R'))){

			//setting the update values
			$values = strtolower($this->pupil) . 'x = \'' . $_POST["form_x"] . '\', ' . strtolower($this->pupil) . 'y = \'' . $_POST["form_y"] . '\'';

			//update table statement
			$sql = 'UPDATE `' . _DB_NAME_ . '`.`imageworkshop` SET ' . $values . ' WHERE `name` = \'' . $this->name . '\'';
			$sql = $this->mysqli->prepare($sql);
			$sql->execute(); 
		}		
	}
	public function deleteCoordinates(){

		$sql = 'UPDATE `' . _DB_NAME_ . '`.`imageworkshop` SET lx = NULL, ly = NULL, rx = NULL, ry = NULL  WHERE `name` = \'' . $this->name . '\'';
		$sql = $this->mysqli->prepare($sql);
		$sql->execute(); 	
	}
	public function pupilForm(){
		return '
		<h1>' . $this->instruction . '</h1>' . $this->displayPupilInformation() . '
		<div class="imageContainer">
			<form name="pointform" method="post">
				<div id="pointer_div" onclick="point_it(event)" style = "background-image:url(\'images/' . $this->img . '\');background-repeat: no-repeat;background-size: contain;">
					<img src="assets/crosshair.png" id="pupil" style="position:relative;visibility:hidden;z-index:2;">
				</div>
				<input type="hidden" name="form_x" size="4" />
				<input type="hidden" name="form_y" size="4" />
				<input type="hidden" name="pupil" value="' . $this->pupil . '">
				<br>
			</form>
		</div>';
	}
	public function displayPupilInformation(){
		return '<div class="pupilInfo"><p><strong>Left:</strong> ' . $this->lx . ' <strong>X</strong> ' . $this->ly . '</p><p><strong>Right:</strong> ' . $this->rx . ' <strong>X</strong> ' . $this->ry . '</p></div>' . $this->button;
	}

	public function pupilSelect(){
		    
		if($this->lx == 0 || $this->ly == 0) {
			$this->pupil = 'L';
			$this->pupilFriendly = 'Left';
			$this->button = null;
			$this->instruction = 'Click on Left-Most Pupil';
			$this->warning = null;

		} elseif($this->rx == 0 || $this->ry == 0) {
			$this->pupil = 'R';
			$this->pupilFriendly = 'Right';
			$this->button = '<form name="deleteCoordinates" method="post"><input type="submit" value="Delete Value(s)"><input type="hidden" value="' . $this->img . '" name="deleteimagevalues"></form>';
			$this->instruction = 'Click on Right-Most Pupil';
			$this->warning = null;

		} else {
			$this->pupil = 'C';
			$this->pupilFriendly = 'COMPLETE';
			$this->button = '<form name="deleteCoordinates" method="post"><input type="submit" value="Delete Value(s)"><input type="hidden" value="' . $this->img . '" name="deleteimagevalues"></form><form name="resizePhoto" method="post"><input type="submit" value="Resize Photo"><input type="hidden" value="' . $this->img . '" name="resize"></form>';
			$this->instruction = 'Click Resize';
			$this->warning = null;

		}
	}

	public function resizePhoto(){
		//initializing new image set
		$imgBase = PHPImageWorkshop\ImageWorkshop::initFromPath('images/'.$this->img);

		//setting dimension variables
		$currentWidth = $imgBase->getWidth();		

		//setting image scale used in resizing and cropping
		$scale = (500/$currentWidth);

		
		//distance between pupils
		$centerLength = (($this->rx / $scale) - ($this->lx / $scale));

		//distance between pupil and side of image
		$offset = ($centerLength/1.2);

		//crop starting position
		$positionX = ($this->lx / $scale) - $offset;
		$positionY = ($this->ly / $scale) - (100 / $scale);
		$position = "LT";
		
		//setting the crop area to be equal to the center area plus 2 offsets
		$cropSize = (($centerLength)+($offset * 2)); 

		//cropping image
		$imgBase->cropInPixel($cropSize, $cropSize, $positionX, $positionY, $position);
 
 		//resizing image
		$imgBase->resizeInPixel(300, 300, true);

		//setting background color for save
		$backgroundColor = 'transparent';

		//getting the resulting image
		$image = $imgBase->getResult($backgroundColor);

		//save settings
		$dirPath = __DIR__ . '/images/resized';
		$filename = $this->name . '_resized.png';
		$createFolders = true;
		$imageQuality = 75; // useless for GIF, usefull for PNG and JPEG (0 to 100%)
 
 		//save new image
		$imgBase->save($dirPath, $filename, $createFolders, $backgroundColor, $imageQuality);	

		$this->newImage = $filename;
	}

	public function displayImage(){
		echo '<img class="resizedImage" src="images/resized/' . $this->newImage . '" alt="Image Name">';
	}
}

?>
<html>
<head>
<script language="JavaScript">
function point_it(event){
    pos_x = event.offsetX?(event.offsetX):event.pageX-document.getElementById("pointer_div").offsetLeft;
    pos_y = event.offsetY?(event.offsetY):event.pageY-document.getElementById("pointer_div").offsetTop;
    document.getElementById("pupil").style.left = (pos_x-10) ;
    document.getElementById("pupil").style.top = (pos_y-9) ;
    document.getElementById("pupil").style.visibility = "visible" ;
    document.pointform.form_x.value = pos_x;
    document.pointform.form_y.value = pos_y;
    document.pointform.submit();
}
</script>

</head>
<style>
html,body {
	margin-bottom: 50px;
	padding-bottom: 50px;
}
.imageContainer {
	width: 510px;
	padding: 5px 0 0 5px;
	border: 1px solid red;
	margin: 0 auto;
}
#pointer_div {
	width: 510px;
	min-height: 700px;
}
.eyeLine {
	width: 300px;
	height: 2px;
	display: block;
	position: absolute;
	top: 140px;
	background: blue;
}
.leftEye, .rightEye {
	width: 18px;
	height: 18px;
	position: relative;
	top: 116px;
	display: block;
	float: left;
	font-size: 10px;
	text-align: center;
	color: white;
	background: url('assets/crosshair.png');
	display: none;
}

.leftEye {left: <?php echo $_COOKIE['leftX']; ?>px; top: <?php echo $_COOKIE['leftY']; ?>px; }
.rightEye {left: <?php echo $_COOKIE['rightX']; ?>px; top: <?php echo $_COOKIE['rightY']; ?>px;}

.resizedImage {
	border: 1px solid #000;
}
</style>
<body>
<?php
require_once("src/PHPImageWorkshop/ImageWorkshop.php");


echo '<a href="index.php" title="back to image selection">Return to Image List</a>';
$image = new imageLoader();


?>

</body>
</html>