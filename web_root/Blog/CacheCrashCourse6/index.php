<?php
	$banner_image = "../CacheCrashCourse/cpu_monochromatic.svg";
	$banner_classes = "banner-fit";
	$banner_alt = "An illustration of a CPU";
	$publish_date = "2020/06/30";
	$post_title = "Cache Crash Course - Conclusion (Part 6)";
	$synopsis = "The conclusion to our deep dive into the CPU cache.";
	$image_credits = [
		"CPU illustration" => ["Many Pixels", "https://www.manypixels.co/" ]
	];
	
	if(isset($is_header)){ return; }
	
	$use_prism = true;
	$use_chartjs = true;
	$use_jsdiff = false;
	
	include '../../components/blog_post_header.php'
?>

<p><b>New here?</b> Here's <a href="/Blog/CacheCrashCourse/">part one</a>, which you should read first if you haven't already.</p>

<p>In the previous sections we explored the impact of the CPU data and instruction caches on performance. We also learned some of the ways the compiler can help you – or hinder you.</p>

<p>So, what have we learned?</p>

<h2>Profile! Profile! Profile!</h2>

<p>On more than one occasion when working on the benchmark I discovered that a seemingly innocuous change non-trivially negatively impacted performance. Nothing illustrates this more clearly than Version 1 to Version 2.</p>

<h2>Predictability is key to unlocking CPU performance</h2>

<p>The performance impact of switching from a list to a vector is astonishing. Given we’re linearly traversing the list in order and accessing every element, this is the best-case performance of both a list and array, so the performance on show here demonstrates the importance of predictability.  Modern CPUs are fantastic at predicting what the read pattern of memory will be.</p>

<p>Now, you might have spotted the random sized allocation in there to simulate something vaguely resembling a normal memory layout rather than a pristine virtual memory space. However, it actually has less impact than you’d think. I was fully expecting to see Version 2 and Version 3 neck-and-neck if we eliminate this randomness, as we’d expect the allocations to be relatively close together, but this isn’t actually the case – at least not on my hardware and OS.</p>

<h2>Don’t update what you don’t need to update</h2>

<p>I didn’t include this in the benchmark itself, but the fastest way to speed up the benchmark itself is to do less work. As a quick test this drops the runtime time to about 0.1ms with all other optimizations applied.
This may seem obvious, but if nothing interacts with your data, it may not need even checking that it needs an update. Likewise, if an object is not visible to the user and can’t move, could you just update it when the user next sees it, and if necessary, account for the lost update time?</p>

<h2>Padding</h2>

<p>The more useful data you can cram into the cache lines you fetch, the fewer you’ll have to wait on. Not every cache miss incurs a stall or immediate penalty, but it may displace other data in the cache that you might need later. This means that optimizing the cache usage in one area may positively impact the cache performance in another.</p>

<h2>Seriously, have you profiled it yet?</h2>

<p>I can’t stress enough how important this is! Likewise, try to ensure the test cases you’re examining are realistic. Is your test representative of how your app/game is likely to be encountered by real users? Don’t optimize cases that don’t matter.</p>

<h2>The end</h2>

<p>We’ve reached the end of our journey into the cache. I hope you learned something along the way because I certainly did!</p>

<p>Stay safe and don’t splash that cache!</p>

<p></p>

<?php include '../../components/blog_post_footer.php' ?>