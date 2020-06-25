<?php
	$banner_image = "../CacheCrashCourse/cpu_monochromatic.svg";
	$banner_classes = "banner-fit";
	$banner_alt = "An illustration of a CPU";
	$publish_date = "2020/06/25";
	$post_title = "Cache Crash Course - Instructions (Part 3)";
	$synopsis = "Part three of our deep dive into the CPU cache and how to optimize for it. Part three covers optimizing your code for better use of the instruction cache.";
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

<p><b>New here?</b> Here's <a href="/Blog/CacheCrashCourse/">part one</a>, which you should read first if you haven't already.</p>

<h2>Branch prediction, speculative execution, and pipelining</h2>

<p>Modern processors are insanely complicated. The TL;DR of this topic is that the processor may decide to predict the future and execute code based on values it doesn’t know yet. It may also be doing multiple things at once, even on the same core.</p>

<p>Here’s a few questions and answers that will explore this complex topic:</p>

<h3>Will a cache miss always slow me down?</h3>

<p>No! Your processor (and compiler) are great at hiding cache misses by reordering your code to hide the cost of the cache miss.</p>

<h3>I heard branches are bad, should I eliminate them?</h3>

<p>No! Sure, eliminate unnecessary branches, but a few are not going to hurt you. Even modern GPUs are fine with branches. The CPU will make a prediction based on which path it thinks a branch will take. It may learn heuristically as your code executes, use hints in code (such as assuming the if statement is more likely than the else), and “secret sauce”. Your CPU may then “speculatively execute” the correct branch, potentially even both paths and keep the result in cache. This means the processor is executing code down a path it doesn’t know is right yet!

<h3>What’s pipelining?</h3>

<p>Your CPU has multiple cores so it can execute multiple things at once. Each core can be executing multiple instructions through its pipeline too.</p>

<h3>How does that work?</h3>

<p>Basically, instructions are split up into stages and the processor can be executing multiple stages at once. Like this diagram shows:</p>

<figure>
	<img src="pipeline.svg" alt="A four stage instruction pipeline.">
	<figcaption>A four stage instruction pipeline. Illustration by <a href="https://en.wikipedia.org/wiki/Instruction_pipelining#/media/File:Pipeline,_4_stage_with_bubble.svg" target="_blank">Cburnett</a></figcaption>
</figure>

<h3>What happens if all the instructions rely on the previous one?</h3>

<p>Then it’ll run each instruction in sequence without pipeline, but that rarely happens.</p>

<h3>Like a load that isn’t in cache?</h3>

<p>Exactly, that’s when cache misses hurt.</p>

<h3>This sounds really complicated</h3>

<p>It is. Here’s a summary: <b>Always Profile Your Changes!</b></p>

<h3>Tell me a joke about pipelining</h3>

<p>No, that instruction is bang out-of-order. I’ll show myself out.</p>

<h2>Instruction cache</h2>

<p>Just as data is cached, so are instructions. My desktop CPU has 32KB of L1 instruction cache (I-cache) per core.<p>

<p>One way to think of what role the I-cache fills is with a hypothetical assembly line worker. One person can do one specific job in an assembly line very well over time as they get better and better at it. Ask that same person to build the entire product though and they won’t be anywhere near as fast while they stop to check what to do next.</p>

<p>A well-organized codebase is typically made up of modules or equivalent organizational structures for convenience and ease of navigation. Combined these may be tens of megabytes of machine code. Taking the example of a game engine, each object in the game may be handled and updated by some central system based on what is relevant to the player at that time. These objects may be updated (or “ticked”) in a variety of orders such as randomly or by insertion order. In most cases they aren’t going to be organized by object type.</p>

<p>Updating a character’s animation will be tens of thousands of lines of code at least. Updating physics for a basic object will also be tens of thousands of lines of code. The Core library of PhysX for example is 4.5MB for some games. That’s a LOT of code. UI, networking, AI, these systems are all getting touched in some capacity during the core game loop.</p>

<p>That 32KB of instruction cache per core is now looking tiny. You’re almost guaranteeing an I-cache miss for every object for every subsystem as your code is far apart in memory space, and almost certainly another for accessing the object itself. A game may have tens if not hundreds of thousands of objects. That’s a lot of data, and a lot of cache misses.</p>

<p>But is there a better way? Of course!</p>

<h2>Aggregation</h2>

<p>For Sea of Thieves we spent a lot of time “aggregating ticks”. Bunching objects of the same type together so we can update them together within our game frame.</p>

<p>My colleague <a href="https://twitter.com/JonMikeHolmes" target="_blank">Jon Holmes</a> did a fantastic presentation on this at Unreal Fest so I won’t dive too deep, but this slide neatly summarizes the gains from just a 100 object aggregation:</p>

<figure>
	<img src="aggregation.jpg" alt="A 30% improvement by aggregating 100 objects together.">
	<figcaption>A 30% improvement by aggregating 100 objects together. Presentation by Jon Holmes at Unreal Fest.</figcaption>
</figure>

<p>Grouping objects like this means the code is in the I-cache, which can have a significant benefit. I highly recommend you watch the rest of Jon’s talk here:</p>

<?php YouTube("CBP5bpwkO54", "Aggregating Ticks to Manage Scale in Sea of Thieves (Unreal Fest Europe 2019)."); ?>

<h2>Inlining</h2>

<figure class="fig-noresize">
	<img src="inline.jpg" alt="INLINE ALL THE THINGS">
	<figcaption>INLINE ALL THE THINGS</figcaption>
</figure>

<p>We all know the virtues of inlining. If we inline all our functions, all our performance problems disappear, right?
Of course not, and in fact aggressive inlining can often be counterproductive.</p>

<p>Now that I’ve primed you to think about instruction caches, let’s think what happens when you inline. Imaginary function A calls function B 10 times, and B is 100 instructions. When you run A, it does some additional work to enter and exit function B 10 times, but your instruction cache now contains one copy of function A and one copy of function B.</p>

<p>When you inline function B, that extra work to enter and exit function B is eliminated, but function A just grew by 10x function B. Your instruction cache now also contains the code for 10x B.</p>

<p>In this scenario, we’d still likely see a performance benefit to inlining B, and in fact the compiler probably did that for you. However, if function B is actually 10,000 instructions and we inline it, things look very different! Now function A is a hulking monstrosity, and we’re getting multiple cache misses for each call of function A. If we don’t inline B, both functions fit in the cache and the entry and exit penalty for B becomes irrelevant by comparison to the size of function B itself.</p>

<p>In most cases the compiler will probably just ignore you and not inline it if you do this. Enter stage left the <code>always_inline</code> (GCC) and <code>__forceinline</code> (MSVC) attributes! These were created as hints to the compiler that you would really like it to inline something, and that you know what you’re doing. Before cracking out the <code>FORCEINLINE</code> macro, are you sure you know what you’re doing?</p>

<p>You may be thinking that nobody in their right mind would force inline a function this big, but it happens, often without realising. Take for example a singleton. You have a Get function that returns (and creates if necessary) an instance of a class on first call. Your Get function is called everywhere, so you force inline it. The code is simple enough:

<pre><code class="language-cpp">FORCEINLINE MyObject& Get()
{
	static MyObject* Singleton = nullptr;
	if(!Singleton)
	{
		Singleton = new MyObject();
		Assert(Singleton);
	}
	return Singleton;
};</code></pre>

<p>Looks good, right? (threading issues aside) You disabled the ability for new to throw by disabling exceptions, so it only seems prudent to assert that it returns a valid value.</p>

<p>What you don’t know is that Assert is a huge macro that expands out and calls multiple functions by writing logs, checking if a debugger is present, sending telemetry etc. On top of that, your log functions are macros so it is always inlined too. To add insult to injury, your memory manager overloads new, and guess what? That’s inlined too!</p>

<p>Suddenly your Get function is a few thousand instructions, and that’s pasted throughout your codebase wherever you need the singleton, and because it’s just a getter you don’t cache the result either!</p>

<p>Yes, inlining can be valuable - force inlining can be too - but use it sparingly and with care. In most cases trust your compiler to get it right, it really does know what it is doing.</p>

<p></p><br />

<p><b>Still here?</b> Here's <a href="/Blog/CacheCrashCourse4/">part four - putting it together and benchmarking it</a>.</p>

<a href="/Blog/CacheCrashCourse4/" class="btn">Part four</a>

<p></p>

<?php include '../../components/blog_post_footer.php' ?>