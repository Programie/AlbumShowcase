<?php
require_once __DIR__ . "/includes/Database.class.php";

$pdo = Database::getConnection();
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo PAGE_TITLE;?></title>

		<link rel="stylesheet" href="css/bootstrap.css" type="text/css"/>
		<link rel="stylesheet" href="css/main.css" type="text/css"/>

		<script type="text/javascript" src="js/jquery.js"></script>
		<script type="text/javascript" src="js/bootstrap.js"></script>

		<script type="text/javascript" src="js/main.js"></script>
	</head>

	<body>
		<div class="container">
			<div class="page-header">
				<h1><?php echo PAGE_TITLE;?></h1>
			</div>
			<div class="row">
				<?php
				$query = $pdo->query("SELECT `id`, `title`, `releaseDate` FROM `albums`");
				while ($row = $query->fetch())
				{
					echo "
						<div class='col-sm-6 col-md-4'>
							<div class='thumbnail album' data-albumid='" . $row->id . "'>
								<img src='coverimages/" . $row->id . ".jpg'/>
								<div class='caption'>
									<h3 class='album-title'>" . htmlentities($row->title) . "</h3>
									<p class='album-releasedate'>" . date(FORMAT_DATE, strtotime($row->releaseDate)) . "</p>
									<p>
										<button class='btn btn-primary' role='button'>Download</button>
										<button class='btn btn-default show-tracklist-button' role='button'>Track list</button>
									</p>
								</div>
							</div>
						</div>
					";
				}
				?>
			</div>
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