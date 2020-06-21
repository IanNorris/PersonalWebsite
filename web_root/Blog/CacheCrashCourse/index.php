<?php
	$banner_image = "cpu_monochromatic.svg";
	$banner_classes = "banner-fit";
	$banner_alt = "An illustration of a CPU";
	$publish_date = "2020/06/30";
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

<p>This piece will provide some tips on making more optimal use of your cache and help you avoid some common pitfalls that can tank your performance. By looking at examples of what isn’t cache friendly, we will learn what is.</p>

<p>This post is aimed at developers familiar with lower-level languages like C++ and assumes some exposure to concepts like dynamic memory allocation and pointers.</p>

<p>As with everything performance related, there is no one-size-fits-all solution that will work for every problem, so always profile before and after changes. Likewise, I’m a human first and programmer second so apologies if I got something wrong here, this is a very complex topic!</p>
									
<h2>Cache lines</h2>

<p>Memory gets increasingly more expensive the faster you need to access it. The amount of cache you have is tied to your specific CPU and has no relation to the amount of RAM you have. Even if you have 16GB of RAM you CPU likely have less than 16MB of cache on your CPU, and unless you have server or workstation grade CPUs, less than 1mb of L1 cache shared across all cores.</p>

<p>CPU-Z says the following about my CPU:</p>

<figure class="fig-noresize">
	<img src="cpuz.png" alt="Output from CPU-Z for the author's CPU">
	<figcaption>A high end desktop CPU with 32KB of cache per core.</figcaption>
</figure>

<p>The “cache line” on a modern processor is typically 64 bytes. The cache line is the minimum granularity of a fetch from main memory into any cache. Only need 10 bytes? Tough! The processor is fetching 64. Want 65 bytes? The CPU is now fetching you 128! Those 64 byte blocks are “aligned”, so if your data structure falls across a boundary, you may pay the cost for several cache lines.</p>

<p>Every time memory is fetched you pay a penalty called a cache miss. If you’re reading a large data structure, you might have several cache misses until all data is loaded. If the bytes you need are spread out, you’re paying a high cost for every byte read. Some cache misses are unavoidable, our goal is to eliminate the avoidable ones.</p>

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

<p></p>

<h2>Data structures for performance</h2>

<p>Bjarne Stroustrup gave a wonderful talk on data structures that you should watch:</p>

<?php YouTube("0iWb_qi2-uI?start=2677", "Bjarne Stroustrup discusses vectors vs linked lists."); ?>

<p>For those that want a quick summary: There are (almost) no scenarios where a linked list wins in performance over a vector. Vectors are faster, even when your N is “Big Data” large.</p>

<p>There are some scenarios where flexibility matters more than performance however. By all means use a linked list, but when performance is your primary goal a vector will (almost) always be faster - even with frequent deletion and insertions.</p>

<p>If you don’t care about order, vectors can be made even faster. You can use a technique called “swap and pop” where you can remove an item from the middle of a list by replacing it with the last element in the vector and erasing the last to prevent “shuffling.”</p>

<p>If you care about pointer (or index) stability, vectors are not great as they can reallocate when resized. A great data structure for this scenario is a <a href="http://www.cplusplus.com/reference/deque/deque/" target="_blank">deque</a>. Allocations are made in “pages” and when you need to expand the data structure you add a page. New items fill in gaps in existing pages, and when a page is empty it is deleted. These can often be a good trade-off between performance and flexibility.</p>

<h2>Types</h2>

<h3>Strings</h3>

<p>Strings typically fit into two categories – fixed length, or dynamic and heap allocated. There are a few exceptions like when using alloca, but we’ll ignore those...</p>

<p>Strings are an inefficient method of storing data, and if you’re using Unicode for English text, you’re wasting over half the bytes the majority of the time! But, there’s a further elephant in the room of strings. If you embed strings into your data structure, you need to allocate the maximum size. And because we’re programmers we like to use powers of two, picking numbers like 32 or 64. Suddenly an embedded string in a data structure takes up an entire cache line. So, do you really need that string?</p>

<p>In the heap allocated case you need a pointer to access it. Considering 64bit is now the standard, that’s 8 bytes for the string in your structure. If you need to use that string you need to dereference it, potentially incurring a cache miss there too.</p>

<p>Unreal Engine has a concept of an FName, which is essentially a hash set of strings that can be compared by index. This is a great idea because it lets you compare two strings with an integer comparison and removes (most) duplication. The downside is that creation of a new FName is expensive, and it never removes them – so be sure you’re using them with care.</p>

<h3>Booleans</h3>

<p>How big a Boolean? We’re storing one bit of data, so one bit, right? The answer is: it depends! What’s your pack set to? What’s the natural alignment of the members before and after? You could find your Boolean is now effectively 4 bytes or worse.</p>

<p>Bitfields are a good option to consider if you want optimal space efficiency. They will let you mix Booleans with other types without padding getting in your way, and the compiler will handle the bit masking for you. However, using them comes at a performance penalty.</p>

<p>As a side note, don’t mix types with bitfields, it may not do what you expect, and behaviour varies across compilers.</p>

<h2>Alignment &amp; Padding</h2>

<p>Certain instructions can only execute on aligned memory. Some instructions perform better when the memory being accessed is aligned. As such most types have what is known as “natural alignment”, which describes the alignment that is optimal for this type.</p>

<p>Take for example this simple structure:</p>

<pre><code class="language-cpp">struct AlignmentTest
{
	char Byte;
	char* Bytes;
	char Byte2;
};</code></pre>

<p>Let’s work out the size of <code>AlignmentTest</code>. We know the alignment for a char is 1, so we don’t need to worry about <code>Byte</code>, but <code>Bytes</code> is a pointer, which needs an alignment of 8 bytes. We have one byte already, so we need to add another 7 of pad in front of it.</p>

<p><code>Bytes</code> itself is 8 bytes so we’re already up to 16 bytes, add 1 byte for a total of 17 and we’re done, right? Nope! What if we have an array? The second element will not aligned! How much do we need to add to make the second element aligned? 7 bytes. For <code>Bytes</code> to be aligned in all elements in the array each structure needs to be 8 bytes aligned. The easiest way to do that is to pad the end of the structure. As such <code>sizeof(AlignmentTest)</code> is actually 24 bytes.</p>

<p>Visualized it looks like this:</p>

<figure class="fig-noresize">
	<img src="structalign.png" alt="The layout of structure AlignmentTest when alignment is applied.">
	<figcaption>The layout of structure <code>AlignmentTest</code> when alignment is applied.</figcaption>
</figure>

<p>Ok, so is 8 bytes the max alignment? Of course not!</p>

<pre><code class="language-cpp">struct AlignmentTest2
{
	char Byte;
	__m128 Vector;
	char Byte2;
};</code></pre>

<p>You know the drill, right? As you probably guessed, <code>__m128</code> is 16 byte aligned. This means both <code>Byte</code> and <code>Byte2</code> need 15 bytes of padding after them. The generalization is that the compiler will align the end of the structure to the alignment of the largest alignment within the type.</p>

<p>If you’ve written (or intend to write) your own allocator, you may be wondering how do you ensure your structure starts aligned?</p>

<p>Luckily, there’s a solution to that so you don’t need to pass that mental load to your user: <a href="http://www.cplusplus.com/reference/type_traits/alignment_of/" target="_blank"><code>alignment_of</code></a>. This is used like so: <code>alignment_of&lt;AlignmentTest2&gt;::value</code>, which returns 16 for <code>AlignmentTest2</code>.</p>

<p>With all this in mind, you can tell a lot about an engineer’s background and primary concerns by how they organize data in their structures and classes.</p>

<p>I’ve seen very senior engineers take very little concern about the order of members; mixing booleans, floats and pointers haphazardly. I’ve also seen obvious attempts to organize by sub-feature or functions that use it. Better, but we can go further.</p>

<p>When size matters you should order with attention to the <a href="https://en.wikipedia.org/wiki/Data_structure_alignment" target="_blank">natural alignment</a> of the member to avoid padding by the compiler. This can help get your total structure size down.</p>

<p>You should use common sense when ordering members however, these are guides not rules, so if you know a set of data will be used together, make sure it will be close by in memory. Eliminating padding is not your primary goal, eliminating excessive waste is.</p>

<p>Because applying these rules is complicated there are often options built into the compiler that will tell you the padding of a structure. My favourite tool to use right now for this is <a href="https://llvm.org/docs/CommandGuide/llvm-pdbutil.html#pretty-subcommand" target="_blank">llvm-pdbutil</a>. This will tell you what’s inside a PDB file from an MSVC compiler (such as included with Visual Studio).</p>

<p>You can use this command to write out all the structures and their memory layout from your application:</p>

<pre><code>llvm-pdbutil.exe pretty -classes -class-definitions=layout Input.pdb > Output.txt</code></pre>

<p>The output for <code>AlignmentTest2</code> looks like this:

<pre><code>struct AlignmentTest2 [sizeof = 48] {
  data +0x00 [sizeof=1] char Byte
  <padding> (15 bytes)
  data +0x10 [sizeof=16] __m128 Vector
	data +0x10 [sizeof=16] float m128_f32[4]
	data +0x10 [sizeof=16] unsigned __int64 m128_u64[2]
	data +0x10 [sizeof=16] char m128_i8[16]
	data +0x10 [sizeof=16] short m128_i16[8]
	data +0x10 [sizeof=16] int m128_i32[4]
	data +0x10 [sizeof=16] __int64 m128_i64[2]
	data +0x10 [sizeof=16] unsigned char m128_u8[16]
	data +0x10 [sizeof=16] unsigned short m128_u16[8]
	data +0x10 [sizeof=16] unsigned int m128_u32[4]
  data +0x20 [sizeof=1] char Byte2
  <padding> (15 bytes)
}
Total padding 30 bytes (62.5% of class size)
Immediate padding 30 bytes (62.5% of class size)</code></pre>

<p>As you can see the padding is where we expected it, and we can also see the internal structure of the <code>_m128</code> type, which is actually a union.</p>

<p>If you want to take a break, now is a great time to go read the story “<a href="/Blog/FireAndFrames/" target="_blank">The fire and the frames</a>”.<p>

<h2>Don’t store what you can calculate faster</h2>

<p>To keep sizes down, are you storing anything trivially calculable? By that I mean the double or half of something or any other low instruction calculation. In fact, we’re typically talking 20–100 cycles for a full cache miss. Obviously every 4b saved does not mean a saved cache miss, far from it, but for trivial calculations you’re probably safe either way. For anything else you’ll need to profile it!</p>

<p>On that note, are you using the best form of your data? For example, if you’re comparing distances, you may want to consider using the square of the distance instead. For comparisons they’re functionally identical, and the bonus is you don’t need to calculate the square root.</p>

<h2>Cache eviction</h2>

<p>A lot of what we’ve talked about so far has implied that the CPU is only capable of caching one thing at a time, but that’s simply not true at all. How processors handle what is in cache is extremely complex, and a lot of the magic is a trade secret too.</p>

<p>The act of displacing something in the cache by loading something new is called cache eviction. Don’t try to worry too much about specific things being evicted, it’s far too complex to even rationalize about. What you should do though is ensure that you minimize the amount of data you need to access at any one time. If your code is reading data from many locations, you’re going to thrash your cache and have a bad time.</p>

<p>For a fun little distraction, read the story “<a href="/Blog/GreySquare/" target="_blank">The grey square of despair</a>”.</p>

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

<?php include '../../components/blog_post_footer.php' ?>