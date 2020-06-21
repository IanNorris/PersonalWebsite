<!DOCTYPE html>

<html lang="en">
	<head>
		<!-- Meta -->
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">
		
		<title>Ian Norris - <?=$page_title?></title>
		
		<link href="/assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
		<link href="/assets/css/main.css" rel="stylesheet">
		<link href="/assets/css/blue.css" rel="stylesheet" title="Color">
		<link href="/assets/css/owl.carousel.css" rel="stylesheet">
		<link href="/assets/css/owl.transitions.css" rel="stylesheet">
		<link href="/assets/css/animate.min.css" rel="stylesheet">
		<link href="/assets/css/aos.css" rel="stylesheet">
		<link href="/assets/css/custom.css" rel="stylesheet">
		
		<link rel="stylesheet" href="/assets/fonts/stylesheet.css" type="text/css" charset="utf-8" />
		
		<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
		<link rel="manifest" href="/site.webmanifest">
		<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
		<meta name="msapplication-TileColor" content="#da532c">
		<meta name="theme-color" content="#ffffff">
		
		<?php if(isset($use_prism) && $use_prism)
		{?>
			<link href="/assets/prismjs/prism.css" rel="stylesheet" />
		<?php } ?>
	</head>
	
	<body>
	
		<header>
			<div class="navbar">
				
				<div class="navbar-header">
					<div class="container">
						<a class="navbar-brand" href="/"><img src="/images/logo_name.png" class="logo" alt=""></a>
						<a class="navbar-toggle btn responsive-menu pull-right" data-toggle="collapse" data-target=".navbar-collapse"><i class="fas fa-bars"></i></a>
					</div>
				</div>
				
				<div class="yamm">
					<div class="navbar-collapse collapse">
						<div class="container">
							<a class="navbar-brand" href="/"><img src="/images/logo_name.png" class="logo" alt=""></a>
							
							<ul class="nav navbar-nav">							
								<?php include 'menu.php' ?>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</header>
		
		<main>