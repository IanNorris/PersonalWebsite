<?php
	$banner_image = "problem_solving_monochromatic.svg";
	$banner_classes = "banner-fit pad-top";
	$banner_alt = "Problem solving";
	$publish_date = "2020/06/10";
	$post_title = "The Grey Square Of Despair";
	$synopsis = "Fresh into the role of a senior programmer and having 'Graphics' in my job title, this is the story of the grey square of despair and my journey to madness and back.";
	$image_credits = [
		"Problem solving" => ["Many Pixels", "https://www.manypixels.co/" ]
	];
	
	if(isset($is_header)){ return; }
	
	include '../../components/blog_post_header.php'
?>

<p><i>This story is true but happened many years ago. As such expect some inaccuracies.</i></p>

<p>I sat in front of a brand new WiiU devkit celebrating. I’d just finished the last few bits of the rendering pipeline to allow us to render a full 3d scene to the screen beyond the previous single triangles and spinning cubes, culminating months of work from our team. Out of the corner of my eye I caught a brief flash on the screen. Odd I thought, but I didn’t see it again.</p>

<p>A few weeks later a member of the gameplay team had got the game running and I went to take a look. It looked great, but he said he’d seen some corruption on screen. Then it happened again, and again.</p>

<p>Time went on and the issue bugged me for months, nagging at me. What struck me as odd is how our test team had never mentioned it once. I occasionally investigated this but made no progress. I investigated memory overwrites and timing issues mostly, but it was clearly an engine issue as I could reproduce it in our rendering unit tests.</p>

<p>At this stage there was extremely limited tooling on the WiiU for graphics debugging so we were basically blind, but our publisher’s Central Tech team had created their own. It was an absolute lifeline but it arrived too late to help us with the majority of our setup pain. However, I was able to capture the corruption using this tool. It was a small grey square. Whenever we entered gameplay the squares appeared more frequently and never in the same place, eventually stopping completely.</p>

<p>I eventually reach out to one of our Central Tech colleagues and asked for help. It took a few emails across timezones, but I eventually sent a capture over. After a few days I got a reply.</p>

<p>They sent me back a code change and a clue to understand it: “You need to invalidate the cache on your swap chain (see attached). Look at the color of the corruption.”</p>

<p>So I did…</p>

<p><img src="Grey.png"/></p>
 
<p>The debugging tool showed R: 0.804, G: 0.804, B: 0.804. Hmm. I threw that into calc: R: 205, G: 205, B: 205. “What’s that in hex?” 0xCD.</p>

<p>Facepalm.</p>

<p>Suddenly the puzzle came together. The issue only happened in debug, not a release build. This bug was writing a pattern of 0xCDCDCD to the front buffer, which was overwritten the next frame.</p>

<p>If you’ve worked on a memory manager before you may have written patterns into your memory to indicate its current state to catch use after frees, uninitialized memory and so on. Ours were 0xCD for newly allocated, 0xFE for freed. So why is 0xCD reappearing in memory allocated to the GPU?</p>

<p>THE CPU CACHE!</p>

<p>When we initialized the game we allocated our buffers used by the renderer using our allocator. Because it was a debug build, we initialized the memory with a pattern to catch bugs. Of course the user never sees this because we render to the screen before we present to the user. The GPU writes to the newly allocated memory, but the CPU still has the pattern we wrote during allocation in its cache. As the game runs those blocks of memory get flushed back to main memory to make room for game data.</p>

<p>All it took to fix was one line of code to invalidate the CPU cache for the memory range we allocated for our GPU buffers. This tells the CPU cache that we don’t care about the contents and to ignore it. And with that, the problem was gone, and I gained a level up.</p>


<?php include '../../components/blog_post_footer.php' ?>