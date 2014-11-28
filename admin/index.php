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

						<div class="btn-group">
							<button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
								<i class="glyphicon glyphicon-user"></i> <span id="user-dropdown-username"></span> <span class="caret"></span>
							</button>
							<ul class="dropdown-menu" role="menu">
								<li role="presentation"><a role="menuitem" href="#" data-toggle="modal" data-target="#change-password"><i class="glyphicon glyphicon-edit"></i> Change Password</a></li>
								<li class="divider"></li>
								<li role="presentation"><a role="menuitem" href="#" data-toggle="modal" data-target="#confirm-logout"><i class="glyphicon glyphicon-off"></i> Logout</a></li>
							</ul>
						</div>
					</div>
				</nav>

				<h3 class="text-muted"><?php echo PAGE_TITLE;?> - Admin Area</h3>
			</div>

			<div id="login">
				<form class="form" role="form" onsubmit="login(); return false;">
					<h2 class="form-heading">Please sign in</h2>

					<p class="label label-danger" id="login-info"></p>

					<input type="text" id="username" class="form-control" placeholder="Username" required="" autofocus=""/>
					<input type="password" id="password" class="form-control" placeholder="Password" required=""/>

					<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
				</form>
			</div>

			<div class="show-loggedin">
				<div class="table-responsive">
					<table class="table table-striped">
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

		<div class="modal fade" id="change-password" tabindex="-1" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title">Change Password</h4>
					</div>
					<form role="form" onsubmit="changePassword(); return false;">
						<div class="modal-body">
							<div class="form">
								<p class="label label-danger" id="change-password-info"></p>

								<input type="password" id="current-password" class="form-control" placeholder="Current password" required="" autofocus=""/>
								<input type="password" id="new-password" class="form-control" placeholder="New password" required="" autofocus=""/>
								<input type="password" id="new-password-confirm" class="form-control" placeholder="Repeat new password" required="" autofocus=""/>
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn btn-primary">Apply</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Chancel</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div class="modal fade" id="confirm-logout" tabindex="-1" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title">Logout</h4>
					</div>
					<div class="modal-body">
						<p>Are you sure to logout?</p>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" id="logout">Yes</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>