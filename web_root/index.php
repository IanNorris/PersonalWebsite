<?php
	$page_title = "Home";
	$modals = [ 
		"sot_pirates" => [
			"type" => "image",
			"title" => "Sea of Thieves Pirates",
			"path" => "images/games/sot/pirate_04_sm.jpg"
		]
	];
	include 'components/page_header.php'
?>

			<section id="hero">
				<div class="container">
					
					<div class="row inner-xs">
						<div class="col-sm-10 center-block text-center">
							<header>
								<h1>Software Engineer</h1>
								<p>I am a passionate and experienced software engineer with a focus on low level systems and core engine technologies. I've worked in the games industry for over 13 years.</p>
							</header>
						</div>
					</div>
					
					<div class="row">
						<div class="col-sm-12">
							<div id="owl-carousel" class="owl-carousel owl-outer-nav owl-ui-lg">
								<div class="item">
									<a href="/Games/Everwild/">
										<figure>
											<figcaption class="text-overlay">
												<div class="info big">
													<h2>Everwild (TBC)</h2>
													<p>Rare, Xbox Game Studios</p>
												</div>
											</figcaption>
											<img src="Games/Everwild/01_banner.jpg" alt="Everwild">
										</figure>
									</a>
								</div>
								
								<div class="item">
									<a href="/Games/SeaOfThieves/">
										<figure>
											<figcaption class="text-overlay">
												<div class="info big">
													<h2>Sea of Thieves (2018)</h2>
													<p>Rare, Microsoft Game Studios</p>
												</div>
											</figcaption>
											<img src="Games/SeaOfThieves/02_banner.jpg" alt="Sea of Thieves">
										</figure>
									</a>
								</div>
								
								<div class="item">
									<a href="/Games/GuitarHeroLive/">
										<figure>
											<figcaption class="text-overlay">
												<div class="info big">
													<h2>Guitar Hero Live (2015)</h2>
													<p>FreeStyleGames, Activision</p>
												</div>
											</figcaption>
											<img src="Games/GuitarHeroLive/01_banner.jpg" alt="Guitar Hero Live">
										</figure>
									</a>
								</div>
								
								<div class="item">
									<a href="/Games/DJHero2/">
										<figure>
											<figcaption class="text-overlay">
												<div class="info big">
													<h2>DJ Hero 2 (2010)</h2>
													<p>FreeStyleGames, Activision</p>
												</div>
											</figcaption>
											<img src="Games/DJHero2/01_banner.jpg" alt="DJ Hero 2">
										</figure>
									</a>
								</div>
							</div>
						</div>
					</div>
					
				</div>
			</section>
			
			<section id="product">
				<div class="container inner-xs">
					
					<div class="row">
						
						<div class="col-sm-6 inner-right-xs text-right aos-init aos-animate" data-aos="fade-up">
							<div class="video-container">
								<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/VYTGWchAlKQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
							</div>
						</div>
						
						<div class="col-sm-6 inner-top-xs inner-left-xs aos-init aos-animate" data-aos="fade-up">
							<h2>Current employment</h2>
							<p>I am currently working as a Senior Software Engineer for <a href="https://www.rare.co.uk/" target="_blank">Rare Ltd</a>, a subsidiary of <a href="https://www.microsoft.com/" target="_blank">Xbox and Microsoft</a>. I work within the Engine team primarily on <a href="https://www.rare.co.uk/news/rare-revelations-at-x019" target="_blank">Everwild</a>.</p>
							<p>I also support <a href="https://www.seaofthieves.com/" target="_blank">Sea of Thieves</a>, a pirate themed "Shared World Adventure Game" (SWAG) where you and your crew sail the seas, go questing and dig up treasure.</p>
							<a href="/Portfolio/SeaOfThieves/" class="txt-btn" data-toggle="modal">Check out Sea of Thieves</a>
						</div>
						
					</div>
			</section>
				

<?php include 'components/page_footer.php' ?>