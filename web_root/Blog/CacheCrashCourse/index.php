<?php
	$banner_image = "cpu_monochromatic.svg";
	$banner_classes = "banner-fit";
	$banner_alt = "An illustration of a CPU";
	$publish_date = "2020/07/07";
	$post_title = "Cache Crash Course";
	$synopsis = "A deep dive into the CPU cache and how to optimize for it.";
	$image_credits = [
		"CPU illustration" => ["Many Pixels", "https://www.manypixels.co/" ]
	];
	
	if(isset($is_header)){ return; }
	
	$use_prism = true;
	
	include '../../components/blog_post_header.php'
?>

<link rel="stylesheet" type="text/css" href="../../assets/jsdifflib/diffview.css">

<style>

#diffoutput {
	width: 100%;
}

table.diff tbody {
	font-family: 'RobotoMono';
	font-size: 12px;
}

table.diff tbody th, table.diff tbody td {
	border: 0;
}

table.diff tbody td, table.diff tbody th {
	padding: 0em 0.5em;
	padding-top: 0em;
}

</style>

<p>Poor cache utilization can cripple your performance and limit your ability to optimize elsewhere. This makes optimizing your cache one of the most impactful changes you can make. This piece will provide some tips on optimizing your cache and help you avoid some common pitfalls that can tank your performance. By looking at examples of what isn’t cache friendly, we will learn what is.</p>

<p>This post is aimed at developers familiar with lower-level languages like C++ and assumes some exposure to concepts like dynamic memory allocation and pointers.</p>

<p>As with everything performance related, there is no one-size-fits-all solution that will work for every problem, so always profile before and after changes. Likewise, I’m a human first and programmer second so apologies if I got something wrong here, this is a very complex topic!</p>

<p>Part one of this extended piece will cover the basics such as how much cache you have, the granularity of the cache and the limits of the compiler's ability to help you.</p>
									
<h2>Cache sizes &amp; cache lines</h2>

<p>Memory gets increasingly more expensive the faster you need to access it. The amount of cache you have is tied to your specific CPU and has no relation to the amount of RAM you have. Even if you have 16GB of RAM you CPU likely have less than 16MB of cache on your CPU, and unless you have server or workstation grade CPUs, less than 1mb of L1 cache shared across all cores.</p>

<p>CPU-Z says the following about my CPU:</p>

<figure class="fig-noresize">
	<img src="cpuz.png" alt="Output from CPU-Z for the author's CPU">
	<figcaption>A high end desktop CPU with 32KB of cache per core.</figcaption>
</figure>

<p>The “cache line” on a modern processor is typically 64 bytes. The cache line is the minimum granularity of a fetch from main memory into any cache. Only need 10 bytes? Tough! The processor is fetching 64. Want 65 bytes? The CPU is now fetching you 128! Those 64 byte blocks are “aligned”, so if your data structure falls across a boundary, you may pay the cost for several cache lines.</p>

<p>When data is fetched from main memory you pay a penalty called a cache miss. If you’re loading a lot of data sequentially, the CPU may be able to predict what you’ll need next by loading it in advance. If you’re reading a large data structure but the data you need is spread out or accessed erratically, you might have several cache misses until all the data is loaded.   In this scenario you’re paying a high cost for every byte read. Some cache misses are unavoidable, our goal is to eliminate the avoidable ones.</p>

<h2>Compiler optimizations</h2>

<p>Compilers are fantastic at identifying dead code and reorganizing to maximize the CPU pipeline, hiding cache latency and much more. However, there’s one thing compilers won’t do – optimise your structures.</p>

<p>It is up to the programmer to identify dead data within structures and classes. The compiler can’t know if you’re going to use the structure for something later (like reading from disk), or if the programmer knows something about memory layout that the compiler doesn’t (for example embedded hardware). As a result, it won’t change your layout beyond the alignment rules we discuss later.</p>

<p>Note, this applies to classes and structures only and not local variables, function parameters and so on. The compiler will happily eliminate those for you, and all the code associated with it if it knows the value is never used.</p>

<h2>Big N, Big :O</h2>

<p>The saying goes that you shouldn’t prematurely optimize, however the larger your N, the more care you need to take with the design of a system. Some design consideration now can play dividends later when system designs are harder to optimize for performance.</p>

<p>Here are a few rules to make your life easier:</p>

<ul>
	<li>Design your external interface to be agnostic of the internal data organization. Avoid exposing internal structures.</li>
	<li>If your system can take ownership (or control creation of) for data it inherits, this will give you more flexibility.</li>
	<li>The fewer places you are allocating the better. Less allocations means less indirection.</li>
	<li>Minimize inheritance and prefer composition. A lot of interfaces on an object means it is over-generalized and will suffer from poor data locality.</li>
	<li>Your classes and structures should do as few things as possible with as little data as possible.</li>
</ul>

<p></p><br />

<p><b>Ready for more?</b> Here's <a href="/Blog/CacheCrashCourse2/">part two - optimizing your data access</a>.</p>

<a href="/Blog/CacheCrashCourse2/" class="btn">Part two</a>

<p></p>

<?php include '../../components/blog_post_footer.php' ?>