<?php
	include 'Peco.class.php';
	$peco = new Peco($_GET['id'], $_GET['zip']);
	print $peco->run();