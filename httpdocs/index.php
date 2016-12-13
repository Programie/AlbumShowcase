<?php
require_once __DIR__ . "/../bootstrap.php";
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo PAGE_TITLE;?></title>

		<link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css"/>
		<link rel="stylesheet" href="css/main.css" type="text/css"/>

		<link rel="icon" type="image/png" href="favicon.png"/>

		<script type="text/javascript" src="bower_components/jquery/dist/jquery.min.js"></script>
		<script type="text/javascript" src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="bower_components/mustache/mustache.min.js"></script>
		<script type="text/javascript" src="bower_components/moment/min/moment-with-locales.min.js"></script>

		<script type="text/javascript" src="js/utils.js"></script>
		<script type="text/javascript" src="js/main.js"></script>

		<script type="text/html" id="tracklist-template">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>#</th>
						<th><?php echo tr("Title");?></th>
						<th><?php echo tr("Artist");?></th>
						<th><?php echo tr("Length");?></th>
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
						<img src="service/albums/{{id}}/cover.jpg"/>
						<div class="caption">
							<p>
								<div class="album-artist">{{artist}}</div>
								<div class="album-title">{{title}}</div>
								<div class="album-releasedate">{{releaseDate}}</div>
							</p>

							<p>
								<a href="service/download/{{id}}/{{title}}.zip" class="btn btn-primary" role="button"><span class="glyphicon glyphicon-cloud-download"></span> <?php echo tr("Download");?>{{#downloadBadge}} <span class="badge">{{downloadBadge}}</span>{{/downloadBadge}}</a>
								<button class="btn btn-default tracklist-button" role="button"><span class="glyphicon glyphicon-th-list"></span> <?php echo tr("Track list");?></button>
							</p>
						</div>
					</div>
				</div>
			{{/list}}
			{{^list}}
				<div class="alert alert-danger">
					<i class="glyphicon glyphicon-exclamation-sign"></i> <strong><?php echo tr("No albums available!");?></strong>
				</div>
			{{/list}}
		</script>
	</head>

	<body>
		<div id="wallpaper"></div>

		<div class="container">
			<nav id="navbar" class="navbar navbar-default">
				<div class="container-fluid">
					<div class="navbar-header">
						<span class="navbar-brand"><?php echo PAGE_TITLE;?></span>
					</div>

					<div class="navbar-form navbar-right">
						<a href="admin"><button role="button" class="btn btn-default"><i class="glyphicon glyphicon-wrench"></i> Login</button></a>
					</div>

					<ul class="nav navbar-nav navbar-right">
						<?php
						$linksFile = APP_ROOT . "/config/links.json";
						if (file_exists($linksFile))
						{
							$linksData = json_decode(file_get_contents($linksFile));
							foreach ($linksData as $linkData)
							{
								echo "<li><a href=\"" . $linkData->url . "\" target=\"" . ($linkData->target ?: "") . "\">" . $linkData->title . "</a></li>";
							}
						}
						?>
					</ul>
				</div>
			</nav>

			<div class="row" id="albums"></div>
		</div>

		<div class="modal fade" id="tracklist" tabindex="-1" role="dialog" aria-labelledby="tracklist-label" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only"><?php echo tr("Close");?></span></button>
						<h4 class="modal-title" id="tracklist-label"></h4>
					</div>
					<div class="modal-body" id="tracklist-content"></div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" data-dismiss="modal"><?php echo tr("Close");?></button>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>