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

		<script type="text/html" id="album-list-template">
			{{#list}}
				<tr class="album-row" data-albumid="{{id}}">
					<td class="album-title">{{title}}</td>
					<td class="album-releasedate">{{releaseDate}}</td>
					<td>
						<button class="btn btn-sm btn-default edit-album"><i class="glyphicon glyphicon-pencil"></i> Edit</button>
						<button class="btn btn-sm btn-danger delete-album"><i class="glyphicon glyphicon-trash"></i> Delete</button>
					</td>
				</tr>
			{{/list}}
		</script>
	</head>

	<body>
		<div class="container">
			<div class="page-header">
				<nav class="show-loggedin">
					<div class="nav nav-pills pull-right">
						<button class="btn btn-sm btn-default" role="button"><i class="glyphicon glyphicon-plus"></i> New Album</button>
						<button class="btn btn-sm btn-default" role="button"><i class="glyphicon glyphicon-off"></i> Logout</button>
					</div>
				</nav>

				<h3 class="text-muted"><?php echo PAGE_TITLE;?> - Admin Area</h3>
			</div>

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

			<div class="show-loggedin">
				<table class="table table-responsive">
					<thead>
						<tr>
							<th>Title</th>
							<th>Release Date</th>
							<th></th>
						</tr>
					</thead>
					<tbody id="album-list"></tbody>
				</table>
			</div>
		</div>

		<div class="modal fade" id="delete-confirmation" tabindex="-1" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title">Delete Album</h4>
					</div>
					<div class="modal-body">
						<p>Are you sure to delete the album <strong id="delete-confirmation-album"></strong>?</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger">Yes, delete it</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>