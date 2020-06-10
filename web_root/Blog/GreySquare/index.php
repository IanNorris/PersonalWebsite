<?php
	$banner_image = "problem_solving_monochromatic.svg";
	$banner_classes = "banner-fit pad-top";
	$banner_alt = "Problem solving";
	$publish_date = "2020/06/10";
	$post_title = "The Grey Square Of Despair";
	$image_credits = [
		"Problem solving" => ["Many Pixels", "https://www.manypixels.co/" ]
	];
	
	if(isset($is_header)){ return; }
	
	include '../../components/blog_post_header.php'
?>

<p>Ian of the past is sitting in front of a brand new WiiU devkit. We were celebrating because I’d just finished the last few bits of the rendering pipeline to allow us to render a full 3d scene to the screen beyond the previous single triangles and spinning cubes. Then suddenly out of the corner of my eye I caught a brief flash on the screen. Odd I thought, but I didn’t see it again.</p>

<p>A few weeks later the game team had got the game running and one of my colleagues called me over to show me. It looked great, but he said he’d seen some corruption on screen. I admitted I’d seen it previously too but wasn’t sure what it was. Then we saw it again, and again.</p>

<p>Time went on and the issue bugged me for months, nagging at me. What struck me as odd is that out test team had never mentioned it once. I occasionally looked at it but made no progress eliminating things like a memory overwrite from our code etc.
At this stage there was extremely limited tooling on the WiiU to do graphics debugging with, but our Central Tech team that served all studios for our publisher had created their own. It was an absolute lifeline but it arrived too late to help us with the majority of our setup pain. However, I was able to capture the corruption using this tool. It was a small grey square. I later caught it again and got two on screen, both small squares. Sure enough, whenever we entered gameplay the squares appeared more frequently and never in the same place.</p>

<p>I eventually reach out to one of our Central Tech colleagues and asked for help. It took a few back and forths, with me making suggestions like a DMA causing it, but I eventually sent a capture over and after a few days I got a reply.
They sent me back a code change and clue to understand it: “You need to invalidate the cache on your swap chain (see attached). Look at the colour value.”</p>

<p>So I did…</p>

<p><img src="Grey.png"/></p>
 
<p>The debugging tool showed R: 0.804, G: 0.804, B: 0.804. Hmm. I threw that into calc: R: 205, G: 205, B: 205. “What’s that in hex?” 0xCD.</p>

<p>Facepalm.</p>

<p>Suddenly the puzzle came together. The issue only happened in debug, not a release build. The issue was writing a pattern of 0xCDCDCD to the front buffer, which was overwritten the next frame.</p>

<p>If you’ve worked with a memory manager before you may write patterns into your memory to indicate its current state. Ours were 0xCD for allocated but not initialized, 0xFE for freed. So why is 0xCD reappearing in memory?</p>

<p>THE CPU CACHE!</p>

<p>When we initialized the game we allocated our buffers used by the renderer. Because it was a debug build, we initialized the memory with a pattern. Of course the user never sees this because we render to the screen before we present to the user. So the GPU writes to the newly allocated memory, but the CPU still has the pattern we wrote during allocation in its cache. As the game runs those blocks of memory get flushed back to main memory to make room for game data.</p>

<p>All it took to fix was one line of code to invalidate the CPU cache for the memory range we allocated for our GPU buffers. And with that, the problem was gone, and with that I gained a level up.</p>


<?php include '../../components/blog_post_footer.php' ?>