<?php
	$banner_image = "../CacheCrashCourse/cpu_monochromatic.svg";
	$banner_classes = "banner-fit";
	$banner_alt = "An illustration of a CPU";
	$publish_date = "2020/06/25";
	$post_title = "Cache Crash Course - Data (Part 2)";
	$synopsis = "Part two of the deep dive into the CPU cache and how to optimize for it. Part two covers optimizing your data and access patterns for better efficiency.";
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

<p>In part one we covered the basics of caching. Now in part two we will cover how to organize your data to make the most efficient use of the cache.</p>

<h2>Data structures for performance</h2>

<p>Bjarne Stroustrup gave a wonderful talk on data structures that you should watch:</p>

<?php YouTube("0iWb_qi2-uI?start=2677", "Bjarne Stroustrup discusses vectors vs linked lists."); ?>

<p>For those that want a quick summary: There are (almost) no scenarios where a linked list wins in performance over a vector. Vectors are faster, even when your N is “Big Data” large.</p>

<p>There are some scenarios where flexibility matters more than performance however. By all means use a linked list, but when performance is your primary goal a vector will (almost) always be faster - even with frequent deletion and insertions.</p>

<p>So why is performance so terrible for linked lists? They lack predictability! CPUs are great at predicting what your code will do next. If you’re traversing a vector it will predict after a few reads that the read pattern is likely to continue. As such you can bet the CPU has already loaded the next element into the cache before you reach the instructions that need it.</p>

<p>The predictability that vectors provide isn’t possible with a traditional linked list because you need the pointer of the next item in the list before you can prefetch the next block of data at that location.</p>

<p>A bonus of using a vector is cache line sharing. If you’ve got a small structure of 32 bytes, you can get two of them in a cache line. When using a linked list, each item is likely to be disparate in memory resulting in each structure occupying its own cache line with the rest being unused.</p>

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

<h2>Object-orientated pitfalls</h2>

<p>The natural organization of data that results from object-oriented programming can make optimization for cache coherency more difficult. OOP is a useful pattern as it can improve readability, but it is important to know when to break with the pattern for performance reasons.</p>

<p>An example of this is a Character class in a game. This code might have rendering, animation, physics, audio, health, and lots more attached to it.</p>

<p>With all this data, your Character class is likely to be large, and if your game has many characters, calling your single Update function is probably going to be quite expensive. Naturally, updating all these systems for a Character is never going to be cheap, but by throwing bad cache utilisation into the mix you’re just compounding the problem.</p>

<p> You can fix some of these problems by breaking down the larger class into smaller components with each component having just the data it needs. The Entity Component System (ECS) pattern describes a good way of doing this. Just be careful you don’t make your components too feature-rich (or generalized) and end up with the same problem...</p>

<h2>Data-oriented design</h2>

<p>If you’ve made it this far, you’ve just been primed on the core concepts of Data-oriented design!</p> 

<p>Mike Acton has been a proponent of Data-oriented Design for years, and I highly encourage you to make your next click his talk on this topic.</p>

<?php YouTube("rX0ItVEVjHc", "Mike Acton - Data-Oriented Design and C++"); ?>

<p><b>There's more!</b> Here's <a href="/Blog/CacheCrashCourse3/">part three - optimizing your code for the cache</a>.</p>

<a href="/Blog/CacheCrashCourse3/" class="btn">Part three</a>

<p></p>

<?php include '../../components/blog_post_footer.php' ?>