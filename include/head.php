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
	width: 99%;
}
body {
	width: 99%;
	padding-bottom: 150px;
	margin: 0 auto;
}
#left {
	float: left;
	width: 45%;
}
#right {
	float: right;
	width: 54%;
}
.imageContainer {
	margin: 0 auto;
}
#pointer_div {
	width: 500px;
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

.pupilInfo input {
	color: #fff;
	font-weight: bold;
	width: 200px;
	text-shadow: 2px 2px 5px #000;
}
.pupilInfo .deleteL input {
	background-color: yellow;
}
.pupilInfo .deleteR input {
	background-color: red;
}
.pupilInfo .deleteA input {
	background-color: blue;
}
.pupilInfo .resize input {
	background-color: green;
}
.frame-container {
	float: left;
	width: 500px;
	border: 1px solid #000;
	height: 500px;
	overflow-y: scroll;
	overflow-x: hidden;
}
.frame-selector {
	width: 100px;
	float: left;
	text-align: center;
	border: 1px solid #000;
	margin: 5px;
	background: #efefef;
	font-size: 10px;
	padding: 0px 0px 5px 0px;
}
.frame-selector img {
	width: 75px;
}
.face-with-frames {
	float: left;
	width: 500px;
}
.footer {
	margin: 10px;
	width: 95%;
	height: 100px;
	color: #000;
	position: absolute;
	bottom: 0px;
}
</style>
<body>