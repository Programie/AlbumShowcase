<?php
require_once __DIR__ . "/includes/config.inc.php";
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo PAGE_TITLE;?></title>

		<link rel="stylesheet" href="css/bootstrap.css" type="text/css"/>
		<link rel="stylesheet" href="css/main.css" type="text/css"/>

		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/bootstrap.js"></script>
		<script type="text/javascript" src="js/mustache.js"></script>
		<script type="text/javascript" src="js/moment.js"></script>

		<script type="text/javascript" src="js/main.js"></script>

		<script type="text/html" id="tracklist-template">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>#</th>
						<th>Title</th>
						<th>Artist</th>
						<th>Length</th>
					</tr>
				</thead>
				<tbody>
					{{#list}}
						<tr>
							<td>{{number}}</td>
							<td>{{title}}</td>
							<td>{{artist}}</td>
							<td>{{length}}</td>
						</tr>
					{{/list}}
				</tbody>
				<tfoot>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th>{{totalLength}}</th>
					</tr>
				</tfoot>
			</table>
		</script>

		<script type="text/html" id="albums-template">
			{{#list}}
				<div class="col-sm-6 col-md-4">
					<div class="well well-sm thumbnail album" data-albumid="{{id}}">
						<img src="albums/{{id}}.jpg"/>
						<div class="caption">
							<h3 class="album-title">{{title}} <small>{{releaseDate}}</small></h3>
							<p>
								<a href="download.php/{{id}}/{{title}}.zip" class="btn btn-primary" role="button"><span class="glyphicon glyphicon-cloud-download"></span> Download{{#downloadBadge}} <span class="badge">{{downloadBadge}}</span>{{/downloadBadge}}</a>
								<button class="btn btn-default tracklist-button" role="button"><span class="glyphicon glyphicon-th-list"></span> Track list</button>
							</p>
						</div>
					</div>
				</div>
			{{/list}}
			{{^list}}
				<div class="alert alert-danger">
					<i class="glyphicon glyphicon-exclamation-sign"></i> <strong>No albums available!</strong>
				</div>
			{{/list}}
		</script>
	</head>

	<body>
		<div class="container">
			<div class="page-header">
				<?php
				if (defined("SHOW_ADMIN_LOGIN") and SHOW_ADMIN_LOGIN)
				{
				?>
					<nav>
						<div class="nav nav-pills pull-right">
							<a href="admin">
								<button class="btn btn-default btn-sm" role="button"><i class="glyphicon glyphicon-wrench"></i> Admin</button>
							</a>
						</div>
					</nav>
				<?php
				}
				?>
				<h3 class="text-muted"><?php echo PAGE_TITLE;?></h3>
			</div>
			<div class="row" id="albums"></div>
		</div>

		<div class="modal fade" id="tracklist" tabindex="-1" role="dialog" aria-labelledby="tracklist-label" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
						<h4 class="modal-title" id="tracklist-label"></h4>
					</div>
					<div class="modal-body" id="tracklist-content"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>