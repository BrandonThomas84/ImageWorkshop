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
	public $degrees;
	public $opposite;
	public $adjacent;

	//image information
	public $id;
	public $name;
	public $newImage;

	//string variables
	public $button;
	public $instruction;
	public $warning;
	
	
	public function imageLoader(){

		require_once("src/PHPImageWorkshop/ImageWorkshop.php");

		if(!isset($_GET['img'])){

			//include the head information
			require_once('head.php');

			//if there is no get and no image return the image sselection list
			echo $this->selectImage();
		} else {

			/*
			//database connection
			$this->mysqli = new mysqli(_DB_SERVER_,_DB_USER_,_DB_PASSWD_,_DB_NAME_);
			if (!$this->mysqli){die("Could not connect to MySQLi: " . mysql_error());}
			*/


			//setting coordinate properties
			$this->setCoordinateProperties();

			//check if an update has been submitted 
			$this->submissionCheck();
			
			//checking if image has been created
			$this->checkForExistingImage();

			echo '<a href="index.php" title="back to image selection">Return to Image List</a>';

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

		//include the head information
		require_once('head.php');

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

		//set the image property
		$this->img = $_GET['img'];

		//removing file extention
		$v = array('.jpg','.jpeg','.png','.gif');

		//setting name property
		$this->name = str_replace($v,'', strtolower($_GET['img']));

		//cookies to be set
		$cookies = array('lx','ly','rx','ry','degrees','opposite','adjacent');

		foreach($cookies as $cookie){
			if(isset($_COOKIE[$cookie])){
				$this->$cookie = $_COOKIE[$cookie];
			}
		}

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
		
	public function updateCoordinates(){
		
		//checking if value being submitted is correct type
		if(in_array($this->pupil, array('L','R'))){

			//setting left and right coordinate value cookies
			setcookie(strtolower($this->pupil) . 'x',$_POST["form_x"]);
			setcookie(strtolower($this->pupil) . 'y',$_POST["form_y"]);
		}	

		if($this->pupil == 'R'){
				
			//setting trig sides
			$this->opposite = (intval($_COOKIE['ly']) - intval($_POST["form_y"]));
			$this->adjacent = (intval($_POST["form_x"]) - intval($_COOKIE['lx']));

			//calculating arc Tangent in radians
			$arcTangent = atan(($this->opposite / $this->adjacent));

			//calculate rotation degrees from arc tangent radians
			$this->degrees = rad2deg($arcTangent);

			//setting degrees cookie
			setcookie('opposite',$this->opposite);
			setcookie('adjacent',$this->adjacent);
			setcookie('degrees',$this->degrees);
		}
	}
	public function deleteCoordinates(){

		$cookies = array('lx','ly','rx','ry','degrees','opposite','adjacent');

		foreach($cookies as $cookie){
			setcookie($cookie,null,time()-100);
		}
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

		//rotating image so eyes are aligned
		$imgBase->rotate($this->degrees);
		
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

		//setting the new image property 
		$this->newImage = $filename;

		//removing stored cookie values
		$this->deleteCoordinates();
	}
	public function displayImage(){
		echo '<br><h1>Resized Image</h1><img class="resizedImage" src="images/resized/' . $this->newImage . '" alt="Image Name">';
	}
}


$image = new imageLoader();

?>

</body>
</html>