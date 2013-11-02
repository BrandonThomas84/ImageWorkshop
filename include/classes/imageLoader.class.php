<?php /* FILEVERSION: v1.0.1b */ ?>
<?php

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

	//page class
	public $page;
	
	
	public function imageLoader(){
		//database connection
		$this->mysqli = new mysqli(_DB_SERVER_,_DB_USER_,_DB_PASSWD_,_DB_NAME_);
		if (!$this->mysqli){die("Could not connect to MySQLi: " . mysql_error());}
		
		//including the image workshop files
		require_once("src/PHPImageWorkshop/ImageWorkshop.php");

		//instantiating new page class (loads header and footer information)
		$this->page = new page;

		//check if a prepared image has been selected
		if(isset($_GET['fc'])){
			
			//set the face variable
			$face = new frameSelector($_GET['fc']);

		} else {

			//run image preparation script
			if(!isset($_GET['img'])){

				//check if an update has been submitted 
				$this->submissionCheck();

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
	}
	public function submissionCheck(){
		//checking for submission of uploaded image
		if(isset($_FILES["imgUpload"])){
			$this->imageUpload($_FILES['imgUpload']);
		}

		//checking for submission of pupil coordinates
		if(isset($_POST["form_x"])){
			$this->updateCoordinates($_POST['pupil']);
			header ('Location: index.php?img=' . $this->img);
		}

		//checking for delete pupil coordinates command
		foreach(array('L','R','A') AS $value){
			if(isset($_POST['deleteimagevalues' . $value])){
				$this->deleteCoordinates($value);
				header ('Location: index.php?img=' . $this->img);
			}
		}

		//checking for resize command
		if(isset($_POST['resize'])){
			$this->resizePhoto();
			header ('Location: index.php?img=' . $this->img);
		}
	}
	public function selectImage(){
		//return array
		$a = array();

		//image directory
		$dirs = scandir('images/face');

		//files to ignor
		$noShow = array(".","..",'resized');

		array_push($a,'
			<h2>Image Preparation</h2>
			<p>Please select an image to prepare for the frame selection process.</p>
			<form name="imagePrepSelection" method="get">
				<select name="img">');

		//for each image display a link with their name
		foreach($dirs AS $file){
			if(!is_dir($file) && (!in_array($file,$noShow))){
				array_push($a,'<option value="' . $file . '">' . strtoupper($file) . '</option>');
			}
		}

		//closing image selector option box and adding upload option form
		//resized image directory
		$prepped = scandir('images/face/resized');

		$v = '
				<input type="submit" value="Select Image">
			</form>
			<br>
			<p>You can also upload an image to be processed</p>
			<form name="uploadImage" action="' . $_SERVER['PHP_SELF'] . '" method="post" enctype="multipart/form-data">
				<label for="file">Select Image:</label>
    			<input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
				<input type="file" name="imgUpload" id="file">
				<input type="submit" name="submit" value="Upload">
			</form>
			<hr>
			<h2>Frame Selection</h2>
			<p>Select from a prepared image below</p>
			<form name="frameSelectorImage" method="get">
				<select name="fc">';

		//adding html to array
		array_push($a,$v);

		//for each image display a link with their name
		foreach($prepped AS $file){
			if(!is_dir($file) && (!in_array($file,$noShow))){
				array_push($a,'<option value="' . $file . '">' . strtoupper($file) . '</option>');
			}
		}

		//closing image selector option box and adding upload option form
		$v = '
				<input type="submit" value="Select Image">
			</form>';

		//adding html to array
		array_push($a,$v);

		//returning page with values
		return $this->page->buildPage(implode('',$a));
	}
	public function imageUpload(){
		//setting file variables
		$imgFile = $_FILES['imgUpload']['tmp_name'];
		$imgType = $_FILES['imgUpload']['type'];
		$imgName = $_FILES['imgUpload']['name'];
		$acceptedTypes = array('image/bmp','image/gif','image/jpeg','image/png','image/tiff');
		$uploadDir = 'images' . DIRECTORY_SEPARATOR . 'face/' . $imgName;


		if (in_array($imgType,$acceptedTypes)) {
			if(move_uploaded_file($imgFile,$uploadDir)){
				echo '<p style="font-size:24px;width:100%;text-align:center;color:green;">Image has been uploaded and is now available for selection below.</p>';	
			} else {
				var_dump($imgFile);
			}
			
		} else {
			echo '<p style="font-size:24px;width:100%;text-align:center;color:red;">File must be a valid image file to upload. ---- ' . $imgType . '</p>';
		}

		
	}
	public function checkForExistingImage(){
		//setting filename
		$file = 'images/face/resized/' . $this->name . '_resized.png';

		//checking if file is present
		if(file_exists($file)){

			//making sure new image variable is set
			$this->newImage = $this->name . '_resized.png';

			//showing image
			$this->page->buildPage($this->displayImage());

		} else {
			
			//display pupil configuration
			$this->page->buildPage($this->pupilForm());
		}
	}
	public function setCoordinateProperties(){

		//set the image property
		$this->img = $_GET['img'];

		//removing file extention for name property
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
			$this->instruction = 'Click on Left-Most Pupil';
			$this->warning = null;

		} elseif($this->rx == 0 || $this->ry == 0) {
			$this->pupil = 'R';
			$this->pupilFriendly = 'Right';
			$this->instruction = 'Click on Right-Most Pupil';
			$this->warning = null;

		} else {
			$this->pupil = 'C';
			$this->pupilFriendly = 'COMPLETE';
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
	public function deleteCoordinates($value){

		if($value == 'A'){
			$cookies = array('lx','ly','rx','ry','degrees','opposite','adjacent');
		} elseif($value == 'L'){
			$cookies = array('lx','ly','degrees','opposite','adjacent');
		} elseif($value == 'R'){
			$cookies = array('rx','ry','degrees','opposite','adjacent');
		}

		foreach($cookies as $cookie){
			if(isset($_COOKIE[$cookie])){
				setcookie($cookie,null,time()-100);
			}
		}
	}
	public function displayPupilPosition(){

		//pupil value options
		$options = array('L','R');

		//return array
		$images = array();

		foreach($options AS $option){

			//setting initial top and left cookie values
			$top = strtolower($option) . 'y';
			$left = strtolower($option) . 'x';

			//setting adjustment variables to compensate for styling
			if($option == 'R'){
				$adjust = 18;
			} else {
				$adjust = 0;
			}

			//checking to see if the pupil variable has been set
			if(isset($_COOKIE[$top])){

				//setting top and left position variable and adjusting for styling (padding and margin)
				$top = ($_COOKIE[$top] - 8);
				$left = ($_COOKIE[$left] - (28 + $adjust));

				//setting the image variable
				$image = '<img src="assets/crosshair' . $option . '.png" class="pupilSet pupil' . $option . '" style="position:relative;z-index:10;top:' . $top . 'px;left:' . $left . 'px;">';

				//adding image variable to the array
				array_push($images,$image);
			}			
		}

		//return images
		return implode('',$images);
	}
	public function pupilForm(){
		return '
		<div id="left">
			<h1>' . $this->instruction . '</h1>' . $this->displayPupilInformation() . '
		</div>
		<div id="right">
			<div class="imageContainer">
				<form name="pointform" method="post">
					<div id="pointer_div" onclick="point_it(event)" style = "background-image:url(\'images/face/' . $this->img . '\');background-repeat: no-repeat;background-size: contain;">
						<img src="assets/crosshair' . $this->pupil . '.png" id="pupil" style="position:relative;z-index:2;visibility:hidden;"> ' . $this->displayPupilPosition() . '
					</div>
					<input type="hidden" name="form_x" size="4" />
					<input type="hidden" name="form_y" size="4" />
					<input type="hidden" name="pupil" value="' . $this->pupil . '">
					<br>
				</form>
			</div>
		</div>';
	}
	public function displayPupilInformation(){

		return '
		<hr>
		<div class="pupilInfo">
			<p>
				<strong>Left:</strong> ' . $this->lx . ' <strong>X</strong> ' . $this->ly . '
				<form name="deleteCoordinatesL" method="post" class="deleteL">
					<input type="submit" value="Delete Left Value">
					<input type="hidden" value="' . $this->img . '" name="deleteimagevaluesL">
				</form>
			</p>
			<hr>
			<p>
				<strong>Right:</strong> ' . $this->rx . ' <strong>X</strong> ' . $this->ry . '
				<form name="deleteCoordinatesR" method="post" class="deleteR">
					<input type="submit" value="Delete Right Value">
					<input type="hidden" value="' . $this->img . '" name="deleteimagevaluesR">
				</form>
			</p>
			<hr>
			<p><strong>Remove Both Values</strong></p>
			<form name="deleteCoordinatesA" method="post" class="deleteA">
				<input type="submit" value="Delete All Values">
				<input type="hidden" value="' . $this->img . '" name="deleteimagevaluesA">
			</form>
			</hr>
			<p><strong>Resize Image</strong></p>
			<form name="resize" method="post" class="resize">
				<input type="submit" value="Resize Photo">
				<input type="hidden" value="' . $this->img . '" name="resize">
			</form>
		</div>
		<br/>';
	}
	public function resizePhoto(){
		//initializing new image set
		$imgBase = PHPImageWorkshop\ImageWorkshop::initFromPath('images/face/'.$this->img);

		//setting dimension variables
		$currentWidth = $imgBase->getWidth();		

		//setting image scale used in resizing and cropping
		$scale = (500/$currentWidth);
		
		//distance between pupils
		$centerLength = (($this->rx / $scale) - ($this->lx / $scale));

		//distance between pupil and side of image
		$offset = ($centerLength);

		//crop starting position
		$positionX = ($this->lx / $scale) - ($offset);
		$positionY = ($this->ly / $scale) - (110 / $scale);
		$position = "LT";
		
		//setting the crop area to be equal to the center area plus 2 offsets
		$cropSize = (($centerLength)+($offset * 2)); 

		//rotating image so eyes are aligned and dividing by another integer (2) to reduce how dramatic the rotation is
		$imgBase->rotate($this->degrees/2);
		
		//cropping image
		$imgBase->cropInPixel($cropSize, $cropSize, $positionX, $positionY, $position);

 		//resizing image
		$imgBase->resizeInPixel(300, 300, true);

		//setting background color for save
		$backgroundColor = 'transparent';

		//getting the resulting image
		$image = $imgBase->getResult($backgroundColor);

		//save settings
		$dirPath = 'images/face/resized';
		$filename = $this->name . '_resized.png';
		$createFolders = true;
		$imageQuality = 75; // useless for GIF, usefull for PNG and JPEG (0 to 100%)
 
 		//save new image
		$imgBase->save($dirPath, $filename, $createFolders, $backgroundColor, $imageQuality);	

		//setting the new image property 
		$this->newImage = $filename;

		//removing stored cookie values
		$this->deleteCoordinates('A');
	}
	public function displayImage(){
		$content = '<br><h1>Resized Image</h1><img class="resizedImage" src="images/face/resized/' . $this->newImage . '" alt="Image Name">';

		$this->page->buildPage($content);
	}
}

?>