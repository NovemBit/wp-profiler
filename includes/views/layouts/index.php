<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $title;?></title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.js"></script>
</head>
<body>


<div class="container" style="margin-top:30px">
    <canvas id="WPPFChart" width="50%" height="10%" ></canvas>
	<?php echo $content; ?>
</div>

</body>
</html>
