<?php
require_once __DIR__ . "/../includes/config.inc.php";
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo PAGE_TITLE;?></title>

		<link rel="stylesheet" href="../css/bootstrap.css" type="text/css"/>
		<link rel="stylesheet" href="../css/datepicker.css" type="text/css"/>
		<link rel="stylesheet" href="../css/admin.css" type="text/css"/>

		<script type="text/javascript" src="../js/jquery.js"></script>
		<script type="text/javascript" src="../js/bootstrap.js"></script>
		<script type="text/javascript" src="../js/mustache.js"></script>
		<script type="text/javascript" src="../js/moment.js"></script>
		<script type="text/javascript" src="../js/datepicker.js"></script>

		<script type="text/javascript" src="../js/jquery-ui-widget.js"></script>
		<script type="text/javascript" src="../js/iframe-transport.js"></script>
		<script type="text/javascript" src="../js/fileupload.js"></script>

		<script type="text/javascript" src="../js/admin.js"></script>

		<script type="text/html" id="album-list-template">
			{{#list}}
				<tr class="album-row" data-albumid="{{id}}">
					<td class="album-title">{{title}}</td>
					<td class="album-releasedate">{{releaseDate}}</td>
					<td>
						<button type="button" class="btn btn-sm btn-default edit-album"><i class="glyphicon glyphicon-pencil"></i> Edit</button>
						<button type="button" class="btn btn-sm btn-danger delete-album"><i class="glyphicon glyphicon-trash"></i> Delete</button>
					</td>
				</tr>
			{{/list}}
		</script>

		<script type="text/html" id="edit-album-tracklist-template">
			{{#.}}
				<tr class="tracklist-row">
					<td><input type="text" class="form-control edit-album-tracklist-number" value="{{number}}"/></td>
					<td><input type="text" class="form-control edit-album-tracklist-title" value="{{title}}"/></td>
					<td><input type="text" class="form-control edit-album-tracklist-artist" value="{{artist}}"/></td>
					<td><input type="text" class="form-control edit-album-tracklist-length" value="{{length}}"/></td>
					<td>
						<button type="button" class="btn btn-danger delete-track"><i class="glyphicon glyphicon-trash"></i></button>
					</td>
				</tr>
			{{/.}}
		</script>
	</head>

	<body>
		<div class="container">
			<div class="page-header">
				<nav class="show-loggedin">
					<div class="nav nav-pills pull-right">
						<button type="button" id="new-album-button" class="btn btn-sm btn-default" role="button"><i class="glyphicon glyphicon-plus"></i> New Album</button>

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

					<div class="form-control-group">
						<input type="text" id="username" class="form-control form-control-large" placeholder="Username" required="" autofocus=""/>
						<input type="password" id="password" class="form-control form-control-large" placeholder="Password" required=""/>
					</div>

					<input class="btn btn-lg btn-primary btn-block" type="submit" value="Sign in"/>
				</form>
			</div>

			<div class="show-loggedin">
				<div class="table-responsive" id="albums-table">
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

				<div class="alert alert-danger" id="no-albums-info">
					<i class="glyphicon glyphicon-exclamation-sign"></i> <strong>No albums available!</strong>
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
						<button type="button" class="btn btn-danger" id="delete-confirmation-button">Yes, delete it</button>
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

								<div class="form-control-group">
									<input type="password" id="current-password" class="form-control form-control-large" placeholder="Current password" required="" autofocus=""/>
									<input type="password" id="new-password" class="form-control form-control-large" placeholder="New password" required="" autofocus=""/>
									<input type="password" id="new-password-confirm" class="form-control form-control-large" placeholder="Repeat new password" required="" autofocus=""/>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<input type="submit" class="btn btn-primary" value="Apply"/>
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

		<div class="modal fade" id="edit-album" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title" id="edit-album-modal-title"></h4>
					</div>

					<form role="form" onsubmit="saveAlbum(); return false;">
						<div class="modal-body">
							<div role="tabpanel">
								<ul class="nav nav-tabs" role="tablist">
									<li role="presentation" class="active"><a href="#edit-album-tab-general" role="tab" data-toggle="tab"><i class="glyphicon glyphicon-cog"></i> General</a></li>
									<li role="presentation"><a href="#edit-album-tab-tracklist" role="tab" data-toggle="tab"><i class="glyphicon glyphicon-th-list"></i> Tracklist</a></li>
									<li role="presentation"><a href="#edit-album-tab-cover" role="tab" data-toggle="tab"><i class="glyphicon glyphicon-picture"></i> Cover</a></li>
									<li role="presentation"><a href="#edit-album-tab-uploadfile" role="tab" data-toggle="tab"><i class="glyphicon glyphicon-cloud-upload"></i> Upload file</a></li>
								</ul>

								<div class="tab-content">
									<div role="tabpanel" class="tab-pane fade in active" id="edit-album-tab-general">
										<label for="edit-album-title">Title</label>
										<input type="text" id="edit-album-title" class="form-control"/>

										<br/>

										<label for="edit-album-releasedate">Release Date</label>
										<input type="date" id="edit-album-releasedate" class="form-control"/>
									</div>

									<div role="tabpanel" class="tab-pane fade" id="edit-album-tab-tracklist">
										<table class="table table-striped">
											<thead>
												<tr>
													<th>#</th>
													<th>Title</th>
													<th>Artist</th>
													<th>Length</th>
													<th></th>
												</tr>
											</thead>
											<tbody id="edit-album-tracklist"></tbody>
										</table>

										<button type="button" class="btn btn-default" id="edit-album-addtrack"><i class="glyphicon glyphicon-plus"></i> Add new track</button>
									</div>

									<div role="tabpanel" class="tab-pane fade" id="edit-album-tab-cover">
										<img id="edit-album-cover"/>

										<div class="progress" id="edit-album-uploadcover-progressbar-container">
											<div role="progressbar" id="edit-album-uploadcover-progressbar"></div>
										</div>

										<input type="file" id="edit-album-uploadcover" name="file"/>
									</div>

									<div role="tabpanel" class="tab-pane fade" id="edit-album-tab-uploadfile">
										<div class="progress" id="edit-album-uploadfile-progressbar-container">
											<div role="progressbar" id="edit-album-uploadfile-progressbar"></div>
										</div>

										<input type="file" id="edit-album-uploadfile" name="file"/>
									</div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<input type="submit" class="btn btn-primary" value="Save"/>
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>