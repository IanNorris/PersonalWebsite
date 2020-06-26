<?php
	$banner_image = "../CacheCrashCourse/cpu_monochromatic.svg";
	$banner_classes = "banner-fit";
	$banner_alt = "An illustration of a CPU";
	$publish_date = "2020/06/25";
	$post_title = "Cache Crash Course - Benchmarking (Part 4)";
	$synopsis = "Part four of our deep dive into the CPU cache and how to optimize for it. Part four puts the concepts together and provides a semi-realistic scenario of cache zero to cache hero.";
	$image_credits = [
		"CPU illustration" => ["Many Pixels", "https://www.manypixels.co/" ]
	];
	
	if(isset($is_header)){ return; }
	
	$use_prism = true;
	
	include '../../components/blog_post_header.php'
?>

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

<p><b>New here?</b> Here's <a href="/Blog/CacheCrashCourse/">part one</a>, which you should read first if you haven't already.</p>

<p>By now you should understand the concepts of caching and be ready to apply it to your own code. In this section we will explore the real-world impact of these changes in a semi-realistic scenario. What most people don't realise is the impact these changes can have - I certainly didn't. I was expecting big wins but the numbers I encountered while writing this benchmark shocked me.</p>

<p>So, what happens if you apply all these rules? Or rather, what happens if we do the exact opposite and then fix it? I wrote a small benchmark that was representative of all the mistakes above, then fixed them all in a series of changes. It’s a bit contrived but bear with me.</p>

<p>These are the rules I stuck to:</p>

<ul>
	<li>No data can be lost. We assume that all the data present in the app is useful to the program, but we don’t care about how that data is accessed (or initialized) outside of our “hot path.”</li>
	<li>This is not an optimization demo, so I can’t modify the core of the algorithm, just how the data it needs is accessed.</li>
	<li>Because we don’t necessarily need to write the result for every object, we’ll pick a subset of objects to write their result back based on their index. You can pretend this is based on the value written or some other Boolean.</li>
</ul>

<h4>Version 1</h4>

<p>Our starting point. Imagine this codebase has evolved over time through several years and developers. All the bad practices seen here I’ve seen in real production code. In fact, this isn’t even that bad by comparison...</p>

<h4>Version 2</h4>

<p>We remove the separate Transform object here resulting in less indirection and put the data it contains next to the other used data. Now, something interesting happens here – the performance actually decreases! We’ll explore Version 2 in more detail later, it’s quite interesting! For now though, know that we’ll want this change later on regardless of its performance impact compared to Version 1.</p>

<h4>Version 3</h4>

<p>Now we swap a std::list for std::vector. That’s the only change. This has the largest performance impact of all the changes. Depending on hardware this is anywhere from 2.5x faster to 60x faster!</p>

<h4>Version 4</h4>

<p>Now we tidy up the mess of data and organize it to reduce padding. Depending on memory bandwidth this can have a decent performance impact as you’re reducing the amount of wasted space from padding in the cache lines fetched.</p>

<h4>Version 5</h4>

<p>Here we swap the 4 byte Boolean type for a one byte Boolean. The history of this <a href="https://stackoverflow.com/questions/54217528/are-there-any-modern-cpus-where-a-cached-byte-store-is-actually-slower-than-a-wo" target="_blank">is complex</a>, but I’ve worked with coding standards that mandated using a 32bit type as a Boolean as a result of the impact of this on some hardware. On modern CPUs using 4 byte Booleans will always be a net loss.</p>

<p>Again, the gains here are minimal unless there are a large number of Booleans in use, in which case the small savings can add up.</p>

<h4>Version 6</h4>

<p>Next, we move the data we don’t need for our “hot path” out into a separate structure but keep a pointer from our primary structure. I found that in some cases this was actually slower (despite our structure decreasing from 656 bytes to 88 bytes), but these remain edge cases. In most cases you will see a performance boost from making this change. This should become obvious if you hit this case though as you’ll have profiled the change, right?</p>

<h4>Version 7</h4>

<p>In version 7 we make additional changes by batching data used together into their individual arrays. The pointer to the “other” data is now separated out and isn’t referenced in the primary structure at all. Instead we rely on the object’s index to access the other data sets.</p>

<p>The result data is also written directly to its own array. We likely get additional benefits here by never reading the result value, only writing it directly to its target location.</p>

<h2>Results</h2>

<p>A quick side note before we examine the results, if you are profiling using Visual Studio, it is really important that even with release builds that you don't run with the debugger attached. Although the severity of the performance hit when a debugger is attached varies between Visual Studio versions (even in release) additional debugging options are enabled that make reproducing bugs more likely (such as the debug allocator and iterator debugging), but these can negatively impact performance. As such when running performance sensitive code (such as this benchmark), run it from outside Visual Studio.</p>

<figure class="fig-noresize">
	<img src="msvc_versions.png" alt="Benchmark results for MSVC">
	<figcaption>Benchmark results for MSVC</figcaption>
</figure>

<p>There are two versions on display here. The first is “partial write back”. In this case we only write back 1% of our results back. This is to simulate a scenario where an object is updating but only sometimes needs to update the stored result. The other scenario is “full write back”, here we always write the result regardless of whether the value changed.</p>

<p>The partial writeback scenario is an interesting one because although I attempted to simulate randomness of objects being updated, the compiler has clearly seen some additional room for optimization in Version 7. Of course, this is only part of the story, see the section about different compilers later!</p>

<p>You can explore the benchmark results further by using the diff tool to compare two versions of the code and results.</p>

<p style="color:red">TODO: Diff here</p>

<!-- ------------------------------------------------------------------------------------ -->
<!--
<div id="diffoutput"> </div>

<script src="../../assets/jsdifflib/difflib.js"></script>
<script src="../../assets/jsdifflib/diffview.js"></script>

<script type="text/javascript">

function blogRender() {
	var codePaths = ["code1.txt", "code2.txt"];
	var codePromises = codePaths.map( url => fetch(url).then( response => response.text() ) );

	Promise.all(codePromises).then( results => {
		var base = results[0].split("\n");
		var next = results[1].split("\n");
		
		var sequenceMatcher = new difflib.SequenceMatcher(base, next);
		
		var opcodes = sequenceMatcher.get_opcodes();
		
		var diffoutputdiv = document.getElementById("diffoutput");
		diffoutputdiv.innerHTML = "";
		
		// build the diff view and add it to the current DOM
		diffoutputdiv.appendChild(diffview.buildView({
			baseTextLines: base,
			newTextLines: next,
			opcodes: opcodes,
			// set the display titles for each resource
			baseTextName: "Left: <select><option>1</option><option>2</option></select>",
			newTextName: "Right: <select><option>1</option><option>2</option></select>",
			viewType: 0
		}));
	} );
};

document.addEventListener('DOMContentLoaded', blogRender, false);

</script>

-->

<?php include '../../components/blog_post_footer.php' ?>