<?php
	$page_title = "Blog";
	$modals = [];
	
	include '../components/page_header.php';
	
	function BlogPost($uri) {
		$is_header = true;
		include "$uri/index.php"
	?>		
		<div class="post format-gallery">
			<div class="row">
				<div class="col-md-4">
					<div class="post-media">
						<figure>
							<a href="<?php echo "$uri/"; ?>">
							<img class="cover-photo <?php echo $banner_classes; ?>" src="<?php echo "$uri/$banner_image"; ?>" alt="<?php echo $banner_alt; ?>" />
							</a>
						</figure>
					</div>
				</div>
				
				<div class="col-md-8">
					<div class="post-content">
						<h1 class="post-title"><a href="<?php echo "$uri/"; ?>"><?php echo $post_title; ?></a></h1>
			
						<ul class="meta">
							<li><?php echo $publish_date; ?></li>
						</ul>
						
						<p><?php echo $synopsis; ?></p>
								
						<a href="<?php echo "$uri/"; ?>" class="btn">Read more</a>
					</div>
				</div>
			</div>
		</div>
	<?php
	}
?>

<section id="blog-post" class="light-bg">
	<div class="container inner-top-sm inner-bottom classic-blog no-sidebar">
		<div class="row">
		
		<?php
			BlogPost('CacheCrashCourse');
			BlogPost('FireAndFrames');
			BlogPost('GreySquare');
		?>
			
		</div>
	</div>
</section>

<?php
	include '../components/page_footer.php'
?>