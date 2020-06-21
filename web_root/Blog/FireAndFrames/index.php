<?php
	$banner_image = "slow_working.svg";
	$banner_classes = "banner-fit pad-top";
	$banner_alt = "A man sitting on a snail working on a laptop";
	$publish_date = "2020/06/10";
	$post_title = "Through The Fire And The Frames";
	$synopsis = "Optimizing slow game code by reorganizing data and not repeating work.";
	$image_credits = [
		"Slow Working" => ["Many Pixels", "https://www.manypixels.co/" ]
	];
	
	if(isset($is_header)){ return; }
	
	$use_prism = true;
	
	include '../../components/blog_post_header.php'
?>

<p><i>This story is true but happened many years ago. As such expect some inaccuracies.</i></p>

<p>A long time ago I was looking for ways to improve our music game’s framerate on one of the consoles we supported. Our test team reported that the hardest track in the game on the hardest difficulty was especially bad. We also noticed that the framerate got progressively lower the further into the song you got. Diligently I opened a profiler, waited until I was close to the end of the track, and hit capture. There was one function right at the top of the list – the code that processed the ‘gems’.</p>

<p>In our game, we had a structure in memory for each note (aka a gem) the player can hit. As with any fairly complex game, our gem structure had amassed a lot of members over time, in particular a lot of Boolean flags. What these flags did I can’t remember, but there were a lot of them. Like 30-40 of them. Combine that with a bunch of floats and misc. other data, and the Gem structure was easily 200 bytes.</p>

<p>If this sounds ridiculous, it did to me as well. I seem to remember screenshotting sizeof(Gem) and sending it to a colleague. So why was it so large?</p>

<p>You see, our coding standards stated that Booleans were bad. I can’t remember the exact reasoning behind this but think something along the lines of Booleans are slower because you need to bitmask them to read them. So instead we used a ‘b32’ Boolean type. Yep, 32 bits for 1 bit of data.</p>

<p>This particular console had low memory bandwidth from main memory, so cache misses really hurt. So, when your gem structure is over 200 bytes and you have a lot of them, you’re going to have a bad time.</p>

<p>I go in and look at the code, and it looks like this:</p>


<pre><code class="language-cpp">
for(int gem = 0; gem < m_gems.count(); gem++ )
{
	if( IsGemVisible(m_gem[gem] ) )
	{
		m_gem[gem].Update(…);
	}
}
</code></pre>

<p>My initial reaction was: why is this always starting at 0 and going all the way to the end? So, I stored the previously earliest shown gem so we could resume from there, updating whenever a gem falls out of the rolling window. I also broke out from the loop once no gems were visible. Seems simple enough, so I boot up the game and… most of the gems were missing!</p>

<p>So, it turns out the gem structure was sorted by gem type first, then by time. It had apparently not occurred to anyone previously that sorting them by time first might be a good idea...</p>

<p>I look into sorting this at load-time but the loading code is complicated and our sort functions all sucked. With an imminent deadline I needed a win right now, so I optimized the Gem structure by bit packing, fixing padding and eliminating unused variables and was able to get it down to about 56 bytes (or something in that ballpark). Suddenly the code ran twice as fast, and the slow down at the end of the tracks wasn’t visible to the player.</p>

<p>As an added bonus I seem to remember this change saving about 2mb of memory – which on a memory constrained console was a big deal.
I later went back and re-exported all the song markup with time sorted data and then re-implemented the gem position caching I’d done, and it worked! The remaining time taken by this function was almost completely eliminated.</p>

<?php include '../../components/blog_post_footer.php' ?>