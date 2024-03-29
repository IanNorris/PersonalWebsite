<?php
	$page_title = "Blog - $post_title";
	$modals = [];
	
	function YouTube($id, $caption) { ?>
		<figure>
			<div class=video-container>
				<iframe src="https://www.youtube-nocookie.com/embed/<?php echo $id; ?>" frameborder="0" allow=accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen="">
				</iframe>
			</div>
			<figcaption><?php echo $caption ?></figcaption></figure>
		<?php
	}
	
	function Chart($src, $caption) { ?>
		<figure>
			<canvas chart="<?php echo $src; ?>"></canvas>
			<figcaption><?php echo $caption; ?></figcaption>
		</figure>
	<?php
	}
	
	include 'page_header.php'
?>

<section id="blog-post" class="light-bg">
	<div class="container inner-top-sm inner-bottom classic-blog no-sidebar">
		<div class="row">
			<div class="post format-gallery">
				<div class="post-content">
					<div class="post-media">
						<figure>
							<img class="cover-photo <?php echo $banner_classes; ?>" src="<?php echo $banner_image; ?>" alt="<?php echo $banner_alt; ?>" />
						</figure>
					</div>
					
					<h1 class="post-title"><?php echo $post_title; ?></h1>
					<h4 class="post-subtitle"><?php echo $synopsis; ?></h4>
					
					<ul class="meta">
						<li><?php echo $publish_date; ?></li>
					</ul>