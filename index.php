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
							<div class='thumbnail'>
								<img src='coverimages/" . $row->id . ".jpg'/>
								<div class='caption'>
									<h3>" . htmlentities($row->title) . "</h3>
									<p>" . date(FORMAT_DATE, strtotime($row->releaseDate)) . "</p>
									<p>
										<button class='btn btn-primary' role='button'>Download</button>
										<button class='btn btn-default' role='button'>Track list</button>
									</p>
								</div>
							</div>
						</div>
					";
				}
				?>
			</div>
		</div>
	</body>
</html>