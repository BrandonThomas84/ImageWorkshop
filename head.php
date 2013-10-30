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