<?php

class frameSelector {
	public $mysqli;
	public $face;
	public $frame;

	//page class
	public $page;

	public function frameSelector($imageName){
		//setting object properties
		
		//database connection
		$this->mysqli = new mysqli(_DB_SERVER_,_DB_USER_,_DB_PASSWD_,_DB_NAME_);
		if (!$this->mysqli){die("Could not connect to MySQLi: " . mysql_error());}
		

		//instantiating new page class (loads header and footer information)
		$this->page = new page;

		//setting the face property
		$this->face = $imageName;

		//showing the frame options
		$this->chooseFrame();

		//checking if frames have been selected
		if(isset($_GET['frm'])){
			//showing the frames
			$this->displayFrame();
		}
	}
	public function chooseFrame(){
		//return array
		$a = array();

		//image directory
		$dirs = scandir('images' . DIRECTORY_SEPARATOR . 'frame' . DIRECTORY_SEPARATOR . 'frames');

		//image file name excess to be removed
		$remove = array('_L','_R','_S','_U','_F','.png');
		
		//removing image excess
		$dirs = str_replace($remove,'',$dirs);
		
		//get distinct list of frames
		$dirs = array_unique($dirs);
		

		array_push($a,'
			<h2>Select Your Frames</h2>
			<p>Please select the frames you would like to preview</p>
			<div class="frame-container">');

		//for each image display a link with their name
		foreach($dirs AS $file){

			//prevent folders from being displayed 
			$blackList = array('..','.','small');

			if(!in_array($file,$blackList)){

				//adding image selectors for each avaiable root frame image
				//NOTE: a duplicate 75px wide small version of the frame must be present in the /small folder
				array_push($a,'
				<a href="index.php?fc=' . $this->face . '&frm=' . $file . '" title="Preview the ' . $file . ' Frames">
					<div class="frame-selector">
						<img src="images' . DIRECTORY_SEPARATOR . 'frame' . DIRECTORY_SEPARATOR . 'frames' . DIRECTORY_SEPARATOR . 'small' . DIRECTORY_SEPARATOR . $file .'_F.png" class="frame">
						<br>' . strtoupper($file) . '
					</div>
				</a>');
			}
		}

		//adding closing frome-container div html to array
		array_push($a,'</div>');

		//returning page with values
		return $this->page->buildPage(implode('',$a));
	
	}
	public function displayFrame(){
		$this->frame = $_GET['frm'] . '_F.png';

		//initializing new image set
		$imgBase = PHPImageWorkshop\ImageWorkshop::initFromPath('images' . DIRECTORY_SEPARATOR . 'face' . DIRECTORY_SEPARATOR . 'resized' . DIRECTORY_SEPARATOR . $this->face);

		//starting new layer group
		$group = $imgBase;

		//adding frames layer
		$frameLayer = PHPImageWorkshop\ImageWorkshop::initFromPath('images' . DIRECTORY_SEPARATOR . 'frame' . DIRECTORY_SEPARATOR . 'frames' . DIRECTORY_SEPARATOR . $this->frame);


		//resizing frames
		$frameLayer->resizeInPixel(300, null, true);

		//clearing any existing face image info
		$imgBaseInfos = null;

		// Left position in px
		$positionX = 0; 

		// Top position in px
		$positionY = -40; 

		//Starting position
		$position = "MM";
		 
		$imgBaseInfos = $group->addLayerOnTop($frameLayer, $positionX, $positionY, $position);

		//setting background color for save
		$backgroundColor = 'transparent';

		//getting the resulting image
		$image = $group->getResult($backgroundColor);

		//save settings
		$dirPath = 'images' . DIRECTORY_SEPARATOR . 'output';
		$filename = $this->face;
		$createFolders = true;
		$imageQuality = 75; // useless for GIF, usefull for PNG and JPEG (0 to 100%)
 
 		//save new image
		$group->save($dirPath, $filename, $createFolders, $backgroundColor, $imageQuality);	

		echo '<div class="face-with-frames"><img src="' . $dirPath . DIRECTORY_SEPARATOR . $this->face . '"></div>';
	}
}

?>