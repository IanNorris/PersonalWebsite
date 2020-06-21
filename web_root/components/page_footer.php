		
		</main>
		
		<footer class="dark-bg">
			<div class="container inner">
				<div class="row">
				
					<div class="col-md-2 col-sm-0 inner">
					</div>
					
					<div class="col-md-4 col-sm-6 inner">
						<h4>Opportunities</h4>
						<p>Although I'm sure your company is great, I'm very happy where I am right now.</p>
					</div>
					
					<div class="col-md-4 col-sm-6 inner">
						<h4>Elsewhere on the Internet</h4>
						<ul class="contacts">
							<li><a href="https://twitter.com/icStatic" target="new"><i class="fab fa-twitter"></i> @icStatic</a></li>
							<li><a href="https://www.linkedin.com/in/iannorrisuk/" target="new"><i class="fab fa-linkedin"></i> Ian Norris</a></li>
							<li><a href="https://www.github.com/IanNorris/" target="new"><i class="fab fa-github"></i> IanNorris</a></li>
						</ul>
					</div>
					
					<div class="col-md-2 col-sm-0 inner">
					</div>
					
				</div>
			</div>
		  
			<div class="footer-bottom">
				<div class="container inner">
					<p class="pull-left">&copy; Ian Norris. All rights reserved. <?php if(isset($image_credits)) { ?><a href="#image-credits" data-toggle="modal">Image credits</a><?php } ?></p>
					<ul class="footer-menu pull-right">
						<?php $simple_menu = true; include 'menu.php' ?>
					</ul>
				</div>
			</div>
		</footer>
		
		<script src="/assets/js/jquery.min.js"></script>
		<script src="/assets/js/jquery.easing.1.3.min.js"></script>
		<script src="/assets/js/jquery.form.js"></script>
		<script src="/assets/js/jquery.validate.min.js"></script>
		<script src="/assets/js/bootstrap.min.js"></script>
		<script src="/assets/js/aos.js"></script>
		<script src="/assets/js/owl.carousel.min.js"></script>
		<script src="/assets/js/jquery.isotope.min.js"></script>
		<script src="/assets/js/imagesloaded.pkgd.min.js"></script>
		<script src="/assets/js/jquery.easytabs.min.js"></script>
		<script src="/assets/js/viewport-units-buggyfill.js"></script>
		<script src="/assets/js/scripts.js"></script>
		
		<?php if(isset($use_prism) && $use_prism)
		{?>
			<script src="/assets/prismjs/prism.js"></script>
		<?php } ?>
		
		<?php 
			if( isset( $modals ) )
			{
				foreach( $modals as $modal_name => $modal_details )
				{
					$modal_title = $modal_details["title"];
					if( $modal_details["type"] === "image" )
					{
						$image_modal_path = $modal_details["path"];
						include( "modals/image_fullscreen.php" );
					}
					else
					{
						include( $modal_details["path"] );
					}
				}
			}
			
			if( isset( $image_credits ) )
			{ ?>
				<div class="modal fade" id="image-credits" tabindex="-1" role="dialog" aria-labelledby="image-credits" aria-hidden="true">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true"><i class="fas fa-times"></i></span></button>
							<h4 class="modal-title" id="modal-contact01">Image credits</h4>
						</div>
						
						<div class="modal-body">
							<div class="container" style="padding-top: 20px; padding-bottom: 20px;">
							<?php
								foreach( $image_credits as $name => $details )
								{?>
									<p><?=$name?> by <a target="_blank" href="<?=$details[1]?>"><?=$details[0]?></a></p>
								<?php } ?>
							</div>
						</div>
						
						<div class="modal-footer">
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
						
					</div>
				</div>
			</div>
		<?php } ?>
	</body>
</html>