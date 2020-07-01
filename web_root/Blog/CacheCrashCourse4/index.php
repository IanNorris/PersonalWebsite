<?php
	$banner_image = "../CacheCrashCourse/cpu_monochromatic.svg";
	$banner_classes = "banner-fit";
	$banner_alt = "An illustration of a CPU";
	$publish_date = "2020/06/27";
	$post_title = "Cache Crash Course - Benchmarking (Part 4)";
	$synopsis = "Part four of our deep dive into the CPU cache and how to optimize for it. Part four puts the concepts together and provides a semi-realistic scenario of cache zero to cache hero.";
	$image_credits = [
		"CPU illustration" => ["Many Pixels", "https://www.manypixels.co/" ]
	];
	
	if(isset($is_header)){ return; }
	
	$use_prism = true;
	$use_chartjs = true;
	$use_jsdiff = true;
	
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
	<li>No data can be lost. All the data present in the app is assumed useful to the program, but we don’t care about how that data is accessed (or initialized) outside of our “hot path.”</li>
	<li>This is not a general optimization demo, so I can’t modify the core of the algorithm, just how the data it needs is accessed.</li>
	<li>Because we don’t necessarily need to write the result for every object, we’ll pick a subset of objects to write their result back based on their index. You can pretend this is based on the value written or some other Boolean. This is controlled by the <code>PARTIAL_WRITEBACK</code> define.</li>
</ul>

<p></p>

<h2>The code</h2>

<p>I've uploaded the code for this <a href="https://github.com/IanNorris/CodeFromBlogPosts/tree/master/Cache" target="_blank">benchmark to Github</a> if you wish to run it on your own machine or do further experiments than what I've outlined here.</p>

<p>The different Version header files in this folder represent the modifiable part of the benchmark, with the rest remaining the same across runs. The interface is a little unusual, but it was structured this way to allow all aspects of the data access to be manipulated across the versions. What follows is a brief summary of the changes made in each version, each iterating from the previous version.</p>

<h4>Version 1</h4>

<p>This is our starting point. Imagine this codebase has evolved over time through several years and developers. All the bad practices seen here I’ve seen in real production code. In fact, this isn’t even that bad by comparison...</p>

<h4>Version 2</h4>

<p>We remove the separate Transform object here resulting in less indirection and put the data it contains next to the other used data. Now, something interesting happens here – the performance actually decreases! We’ll explore Version 2 in more detail later, it’s quite interesting! For now though, know that we’ll want this change later on regardless of its performance impact compared to Version 1.</p>

<h4>Version 3</h4>

<p>Now we swap a std::list for std::vector. That’s the only change. This has the largest performance impact of all the changes. Depending on hardware this is anywhere from 2.5x faster to 60x faster! This change demonstrates the impact of memory read predictability on performance.</p>

<h4>Version 4</h4>

<p>Now we tidy up the mess of data and organize it to reduce padding. Depending on memory bandwidth this can have a decent performance impact as you’re reducing the amount of wasted space from padding in the cache lines fetched. My high end CPU and fast memory throughput diminish the perceived gain here however.</p>

<h4>Version 5</h4>

<p>Here we swap the 4 byte Boolean type for a one byte Boolean. The history of why this might have made it into coding standards <a href="https://stackoverflow.com/questions/54217528/are-there-any-modern-cpus-where-a-cached-byte-store-is-actually-slower-than-a-wo" target="_blank">is complex</a>, but I’ve worked with coding standards that mandated using a 32bit type as a Boolean.</p>

<p>Again, the gains here are minimal unless there are a large number of Booleans in use, in which case the small savings can add up.</p>

<h4>Version 6</h4>

<p>Next, we move the data we don’t need for our “hot path” out into a separate structure but keep a pointer from our primary structure. I found that in some cases this was actually slower (despite our structure decreasing from 656 bytes to 88 bytes), but these remain edge cases. In most cases you will see a performance boost from making this change. This should become obvious if you hit this case though as you’ll have profiled the change, right?</p>

<h4>Version 7</h4>

<p>In version 7 we make additional changes by batching data used together into their individual arrays. The pointer to the “other” data is now separated out and isn’t referenced in the primary structure at all. Instead we rely on the object’s index to access the other data sets.</p>

<p>The result data is also written directly to its own array. We likely get additional benefits here by never reading the result value, only writing it directly to its target location.</p>

<p>If there was padding in our structure at this point we might consider splitting each individual member into its own array and keep the arrays in sync. When we have no padding or alignment however, and always read all the data, there's no performance benefit in doing this. If this wasn't the case we might get a further speed up by doing this.</p>

<h2>Results</h2>

<p>A quick side note before we examine the results, if you are profiling using Visual Studio, it is important that even with release builds that you don't run with the debugger attached (profiler is fine, just the debugger). Although the severity of the performance hit when a debugger is attached varies between Visual Studio versions, additional debugging options are enabled that make reproducing bugs more likely (such as the debug allocator and iterator debugging), but these can negatively impact performance. As such when running performance sensitive code (such as this benchmark), run it from outside Visual Studio.</p>

<?php Chart( "msvc_basic_chart.json", "Benchmark results for MSVC (Microsoft Visual C++)" ); ?>

<p>Below is a diff tool to help you compare the code between the versions for easier comparison against the results above.</p>

<div id="diffoutput"> </div>

<p></p><br />

<p><b>If you enjoyed this, how about <a href="/Blog/CacheCrashCourse5/">part five - the compiler did <i>what</i>?!</a></p>

<a href="/Blog/CacheCrashCourse5/" class="btn">Part five</a>

<p></p>

<script type="text/javascript">

var versionNames = [ "Version 1", "Version 2", "Version 3", "Version 4", "Version 5", "Version 6", "Version 7" ];
var versionPaths = [ "Version1.h", "Version2.h", "Version3.h", "Version4.h", "Version5.h", "Version6.h", "Version7.h" ];

function rerenderDiffWithSelections(left, right){
	var leftOptionsString = "";
	var rightOptionsString = "";
	for(var version = 0; version < versionNames.length; version++ ){
		var leftSelected = left == version ? 'selected="selected"' : '';
		var rightSelected = right == version ? 'selected="selected"' : '';
		leftOptionsString += `<option value="${left}" ${leftSelected}>${versionNames[version]}</option>`;
		rightOptionsString += `<option value="${right}" ${rightSelected}>${versionNames[version]}</option>`;
	}

	var codePaths = [versionPaths[left], versionPaths[right]];
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
			baseTextName: `Left: <select id="diff_select_left" class="select-diff" onchange="rerenderDiff()">${leftOptionsString}</select> <button class="btn btn-diff" onclick="previousDiff()">Previous</button>`,
			newTextName: `Right: <select id="diff_select_right" class="select-diff" onchange="rerenderDiff()">${rightOptionsString}</select> <button class="btn btn-diff" onclick="nextDiff()">Next</button>`,
			viewType: 0
		}));
	} );
}

function rerenderDiff() {
	var left = document.getElementById("diff_select_left").selectedIndex;
	var right = document.getElementById("diff_select_right").selectedIndex;
	
	rerenderDiffWithSelections(left, right);
}

function previousDiff() {
	var left = document.getElementById("diff_select_left").selectedIndex;
	if( left != 0 ) {
		left--;
	
		document.getElementById("diff_select_left").selectedIndex = left;
		document.getElementById("diff_select_right").selectedIndex = left+1;
		
		rerenderDiff();
	}
}

function nextDiff() {
	var right = document.getElementById("diff_select_right").selectedIndex;
	if( right != versionNames.length - 1 ) {
		right++;
	
		document.getElementById("diff_select_left").selectedIndex = right-1;
		document.getElementById("diff_select_right").selectedIndex = right;
		
		rerenderDiff();
	}
}

function diffRender() {
	var left = 0;
	var right = 1;
	
	rerenderDiffWithSelections(left, right);
};

document.addEventListener('DOMContentLoaded', diffRender, false);

</script>

<?php include '../../components/blog_post_footer.php' ?>