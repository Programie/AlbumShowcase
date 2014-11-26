<?php
require_once __DIR__ . "/../includes/config.inc.php";
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo PAGE_TITLE;?></title>

		<link rel="stylesheet" href="../css/bootstrap.css" type="text/css"/>
		<link rel="stylesheet" href="../css/admin.css" type="text/css"/>

		<script type="text/javascript" src="../js/jquery.js"></script>
		<script type="text/javascript" src="../js/bootstrap.js"></script>
		<script type="text/javascript" src="../js/mustache.js"></script>
		<script type="text/javascript" src="../js/moment.js"></script>

		<script type="text/javascript" src="../js/admin.js"></script>
	</head>

	<body>
		<div class="container">
			<div id="login">
				<form class="form-login" role="form" onsubmit="login(); return false;">
					<h2 class="form-login-heading">Please sign in</h2>

					<p class="label label-danger" id="login-info"></p>

					<label for="username" class="sr-only">Username</label>
					<input type="text" id="username" class="form-control" placeholder="Username" required="" autofocus="">

					<label for="password" class="sr-only">Password</label>
					<input type="password" id="password" class="form-control" placeholder="Password" required="">

					<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
				</form>
			</div>
		</div>
	</body>
</html>