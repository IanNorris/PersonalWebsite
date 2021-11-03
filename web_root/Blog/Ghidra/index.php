<?php
	$banner_image = "security.svg";
	$banner_classes = "banner-fit pad-top";
	$banner_alt = "A burglar sneaking in front of an application";
	$publish_date = "2021/11/03";
	$post_title = "Reverse engineering with Ghidra";
	$synopsis = "Exploring reverse engineering by breaking the copy protection of my own software.";
	$image_credits = [
		"Security" => ["Many Pixels", "https://www.manypixels.co/" ]
	];
	
	if(isset($is_header)){ return; }
	
	include '../../components/blog_post_header.php'
?>

<p>In this post we’re going to explore reversing software using the NSA's reverse engineering tool Ghidra. “Reversing” software is a legal grey area and may be illegal in your country depending on your copyright laws, whether copy protection is applied, the software's EULA and so on. With the explicit consent of the software author and copyright owner (that I am granting to you for the purposes of this post) this is perfectly legal though. The software being reversed here was developed by myself nearly two decades ago.</p>

<p>This is very much an <i>introduction</i> to reverse engineering, but despite that we’re going to be at the assembly level from the outset. We’ll be looking at x86 assembly rather than more modern x86-64 flavours (because this software predates x86-64), although the concepts are identical. The path taken here meanders a bit and isn't the most efficient way to understand and bypass the copy protection on this software, but this is done to illustrate a few extra concepts than if I'd just skipped to a more optimal solution.</p>

<h2>The target</h2>

<figure class="fig-noresize">
	<img src="DirList.png" alt="A directory listing of cat and dog pictures.">
	<figcaption>What better way to demonstrate a web server than with some cat and dog pictures?</figcaption>
</figure>

<p>icTransfer was started when I was in high school. It is actually version 2 of a previous attempt called NServer. These projects were among my first attempts at non-trivial software. NServer was very basic and could serve simple content, but I wanted it to do more.</p>

<p>Once my family upgraded to broadband (a mind blowing 512Kbps!), I wanted to be able to access my computer from school, so I created icTransfer with the aim of adding more advanced features such as CGI. Over time I implemented (almost) the full HTTP 1.1 spec, including advanced features like resuming of downloads (which at the time Apache didn’t seem to support). icTransfer also had a GUI to configure it, making setup a breeze compared to Apache, especially at the time. icTransfer also supported SSL, and that too was easy to set up!</p>

<figure class="fig-noresize">
	<img src="Config.png" alt="A screenshot of one of the configuration pages.">
	<figcaption>A sample configuration page. I started a web based configuration but never finished it.</figcaption>
</figure>

<p>I continued to work on icTransfer until I went to university, where it got mothballed. The server could be crashed remotely, and I was unable to reproduce it in a debugger or get a crashdump - the only reproductions I had were from external attacks on the server. So I never released the software. Even at the time I knew these could probably be exploited to run remote code (although not how to do it), so it just sat there taunting me.</p>

<p>As interesting as icTransfer is as exploration of my earlier journey into software development, what made me dig the project out of “Old Projects\Legacy Code” was its copy protection. icTransfer had a rudimentary attempt at copy protection so I could “license” it to my friends, or make some money from it. It was developed when I had a crude interpretation of how people cracked software, but without the concepts to really put up any kind of fight against it. When I got to university a friend wanted to try the software (during a LAN party) and I mentioned it needed a license. This got him really interested and he then spent a few hours attempting to break it. I don’t remember if he succeeded or not – I think he did? (Hey Chris if you're reading this!)</p>

<figure class="fig-noresize">
	<img src="LicenseInstaller.png" alt="icTransfer final installation step.">
	<figcaption>After running it as Administrator we've got it installed! So let's run it!</figcaption>
</figure>

<figure class="fig-noresize">
	<img src="LicenseCheck.png" alt="Message Box stating: icTransfer Was Not Registered For This Machine. Please re-run icTransfer in application mode and insert your license.">
	<figcaption>If you run icTransfer you'll get this fatal error after some Key generation errors first.</figcaption>
</figure>

<figure class="fig-noresize">
	<img src="InvalidLicense.png" alt="Gosh darn it, we need a license!">
	<figcaption>Interestingly if you run icTransfer as Administrator, you get this error. Amusingly the Close button opens it right back up again. How did I notice this? Windows Sandbox runs everything as Administrator. I've put a note towards the bottom on this if you're going to try run this within Windows Sandbox.</figcaption>
</figure>

<p>Because I wrote this, I already have a vague idea of how the copy protection works, but as part of this exercise I've purposefully not looked at the code ahead of time. Where would be the fun if I did?</p>

<h2>Ghidra</h2>

<p>Whether it is safe to run Ghidra or not on your computer is a topic for a whole other blog post, but my TL;DR is that this software is open source, and if the NSA wanted to hack your computer they wouldn’t do it through the highest profile software release that they’ve ever done, targeted at the exact audience to detect such hacks. The software has also been out for a while now, so unless they’re targeting specific users, you’re safe. And if they are targeting you, you’re probably already hacked by some other method.</p>

<p>You can install Ghidra from <a href="https://ghidra-sre.org/" rel="noopener" target="_blank">the website</a>, and I won’t go through the install steps again, but when you eventually launch it you’ll get a lovely splash screen that looks like this:</p>

<figure class="fig-noresize">
	<img src="GhidraSplash.png" alt="A splash screen of a ouroboros snake eating itself">
	<figcaption>Are you excited yet?</figcaption>
</figure>

<p>First off we need to create a project. Because you're reading a post on reverse engineering, you've probably already gone and done that! If not, do so now.</p>

<figure class="fig-noresize">
	<img src="DragExeIn.png" alt="Dragging the icTransfer binary into the project">
	<figcaption>Dragging the icTransfer binary into the project.</figcaption>
</figure>

<p>Once the exe is in the project, double click it to open it. Once opened, it will ask you if you'd like to analyse it. Of course you do! You'll get presented with a bunch of options, the defaults are fine for this project.

Once that finishes (check the progress bar in the bottom right), you should have something that looks like this:

<figure class="fig-fit">
	<img src="GhidraInitial.png" alt="The Ghidra environment">
	<figcaption>THIS! Is the 2020 NSA Ghidra. And it is one of THE most impressive reverse engineering tools available today. It's also free! Today, I'm going to take you on a tour of Ghidra, and show you all of its quirks and features.</figcaption>
</figure>

<p>The first thing you're actually going to want to do at this point is hit the Save button. As you explore the binary, you'll want to rename functions and variables to help you get your bearings, and saving this info periodically is a good idea.</p>

<p>Whenever you modify the flow of the application itself as we begin actually modifying it, you'll periodically want to save new copies of the binary into the project so that you can go back to earlier revisions if necessary. This is really useful especially when you're new to reverse engineering and Ghidra, because it can be quite easy to make a bit of a mess.</p>

<p>A few highlights of the UI to draw your attention to:</p>

<ul>
<li>In the top left on the toolbar you'll see two arrows. Normally green, these act like browser forwards/backwards buttons do, and are really useful to get back to where you came from as you explore functions. You can also use Alt+Left or the back button on your mouse.</li>
<li>On the middle left there's the Symbol Tree. You can explore the functions there, and for larger programs you'll want to use this for navigation. A quirk here is that it groups symbols with the same prefixes together, so an ellipsis at the end means the folder contains several items starting with the same name. When you start out, ALL your functions will start FUN_.... - fun!</li>
<li>Middle left, is the Listing window. This is where your assembly appears.</li>
<li>Middle right, is where Ghidra dumps all the other windows when you create them by default, including the program disassembly/Decompiler output. If you get lost, look for the Decompiler tab.</li>
<li>The Window menu contains all the really useful functionality.</li>
</ul>

<h2>So, about that error message...</h2>

<p>Looking back at the task at hand, we've been presented with a license check error. Our first port of call is to see if that string exists in the binary. Go to the Search menu, select Memory, select String, and type in a subset of the string from the dialog. You could also use the Defined Strings window if you prefer.</p>

<figure class="fig-noresize">
	<img src="SearchMemory.png" alt="Searching memory for the error message">
	<figcaption>Finding the string in memory</figcaption>
</figure>

<p>We've got one hit!</p>

<figure class="fig-fit">
	<img src="ErrorStringMatches.png" alt="A single result for our string search">
	<figcaption>Our search yields just one result.</figcaption>
</figure>

<p>Left click to select the address, and the Listing window will update to show it.</p>

<figure class="fig-fit">
	<img src="StringInTheBinary.png" alt="Our listing showing our defined string">
	<figcaption>That's the badger!</figcaption>
</figure>

<p>Right click the symbol, then References -> Show References to...</p>

<figure class="fig-fit">
	<img src="ErrorReferences.png" alt="One hit of the string being used">
	<figcaption>This is going to be easy isn't it?</figcaption>
</figure>

<p>Now click the reference, and your Listing will update. Your Decompile window will also spring to life and you'll have some rather manky looking C code appear there. The Decompile window and listing windows are probably out of sync, so click any other line then click back to the line highlighted again, and the Decompile window should take you to the correct location.</p>

<figure class="fig-fit">
	<img src="DecompileOutput.png" alt="Where our string is used">
	<figcaption>This is where the message box is displayed from.</figcaption>
</figure>

<p>There's the MessageBox call, with our string passed in!</p>

<p>If you look in the margin on the left and scroll up and down you'll see a bunch of arrows. As you've probably already guessed these indicates the control flow. Solid lines indicate an unconditional jump, whereas dotted lines are conditional. Any bookmarks you've added will also show up here as ticks.</p>

<p>Now is a good time to go to your Bookmarks page, clear out any junk in there (Ghidra highlights interesting things during analysis). You'll want to bookmark the else, which'll mean you can hover on the tick in the margin to see the description. It can be easy to get lost in complex code without proper function or variable names, especially if you're looking at the decompiled output of an optimized release exe. Reduce your mental load and make notes with comments as you go, and rename functions once identified. Right now we don't need the context, we just want to be able to get back here again easily, so right click and add a bookmark to the CALL .... [... MessageBoxA] line.</p>

<p>Have a quick scan around the decompiled function. There are lots of calls to unknown functions (and some known), but there are also lots of strings that can give us context without much effort to understand the code itself. The function parameters can also be a useful clue as to their purpose. In this case there are some interesting strings towards the top (s_-SERVICE, s_-service_rebooting etc). If you double click these in the decompiled code, it'll take you to the definition "-SERVICE" - as alluded to by the name. These are command line parameters.</p>

<p>The function call tree also indicates that "entry" calls this function. If this isn't already open, open it from Window -> Function Call Trees ... . Being called from "entry" means we're pretty close to the start of the app. Handling command line parameters indicates the program is still initializing, so we're probably in a main function. Go right to the top and see what parameters it takes. See the HINSTANCE parameter? That's a tell-tale clue that this is WinMain. Let's rename our function as such. Click the function name (FUN_...) and click Rename Function (or L key). It will have highlighted the function name for you, so you can type Main and press enter to confirm.</p>

<figure class="fig-noresize">
	<img src="Main.png" alt="Renaming our WinMain function">
	<figcaption>We've found WinMain and renamed it as such.</figcaption>
</figure>

<p>Before we start editing things, let's get our bearings and identify the interesting bits. It's fairly long, but not overly complex in flow. Scrolling through the code the following block stood out:</p>

<pre><code class="language-cpp">_DAT_0041c230 = RegisterWindowMessageA(s_ICT_RESET_MESSAGE_USERS_0041a3a0);
_DAT_0041c234 = RegisterWindowMessageA(s_ICT_RESET_MESSAGE_ALIASES_0041a3b8);
_DAT_0041c238 = RegisterWindowMessageA(s_ICT_RESET_MESSAGE_CGI_0041a3d4);
_DAT_0041c23c = RegisterWindowMessageA(s_ICT_RELOAD_INTERFACE_0041a3ec);
_DAT_0041c240 = RegisterWindowMessageA(s_ICT_HIDE_INTERFACE_0041a404);
_DAT_0041c22c = RegisterWindowMessageA(s_icTransferDaemonReceiveMessage_0041a418);
_DAT_0041c244 = RegisterWindowMessageA(s_ICT_QUERY_STATUS_0041a438);
_DAT_0041c248 = RegisterWindowMessageA(s_ICT_SHOW_CONN_MGR_0041a44c);
DAT_0041c24c = RegisterWindowMessageA(s_ICT_REG_CODE_QUERY_0041a460);
</code></pre>

<p>This is clearly UI related, so this branch is likely after the license check. Scroll up looking for anywhere the code could branch away from this path or call into other functions we haven't identified yet, until you hit this.</p>

<figure class="fig-fit">
	<img src="Expired.png" alt="Some code talking about trial licenses">
	<figcaption>Here's some code talking about expired trial licenses. We can also see a function that casts null to a HWND, so it is probably GUI related.</figcaption>
</figure>

<p>Double click the function with the HWND casted parameter (FUN_004096f0). Scroll down the generated code and you'll see references to s_SPLASH being assigned to lpszClassName. We found our splash screen! If you read through the code a bit, you'll see it creates the window, and if it succeeds, it sets a timer then calls another function. If you investigate that, it's calling GetMessage, TranslateMessage, DispatchMessage, so that's a message loop function.</p>

<p>You'll notice here that Ghidra has inferred the local variable names from the functions they are passed to. So you can see a value for Y calculated along with nWidth and nHeight.</p>

<p>Now lets rewind a bit and try to simplify our code path so we can make WinMain look more readable. First off lets get rid of that "not registered" message. Let's navigate back to the bookmark we added earlier. If there's no bookmarks tab, open it from the Window menu and click to that one.</p>

<p>Take a look at the assembly at the else again. We know its going to CALL a Windows function MessageBoxA (side note, for those not familiar A is the suffix used for ASCII versions of Windows functions, whereas Unicode versions use a W suffix - this is normally hidden in C by a macro that points to the right one). We're now interested in what causes our program to get here, and reasons why it might not. In the left margin we can see some arrows coming and going from the surrounding area.</p>

<figure class="fig-fit">
	<img src="BranchAway.png" alt="An interesting branch">
	<figcaption>A JZ instruction - jumps on zero.</figcaption>
</figure>

<p>Our code here will fall through into our error message, which we don't want it to do. Instead we want it to always jump away from the error. Right click the JZ instruction and click Patch Instruction. <b>BUT</b> before we do that, go to the file menu and save a new version of our binary so we've got a convenient place to roll back to when it goes pear-shaped.</p>

<figure class="fig-noresize">
	<img src="Patching1.png" alt="Our target">
	<figcaption>Imma let you finish, but JMP is the best instruction of all time.</figcaption>
</figure>

<p>Use Ctrl+Left to move your cursor past whole words rapidly and enter the mnemonic field and change JZ to JMP to change our instruction from a conditional jump to an unconditional jump. Then press enter. As you do, watch the decompiler output. The if statement just vanished, as has our else clause!</p>

<figure class="fig-noresize">
	<img src="Patching2.png" alt="Choices choices...">
	<figcaption>That list of options are the bytes it'll change the instruction to. Notice how the column before it also has two byte values? Those are the bytes for this instruction mnemonic. Our selected option also has two bytes, this means the instruction will fit as is.</figcaption>
</figure>

<p>Now lets repeat the process for the message box we're going to hit next a few lines down from our current location.</p>

<figure class="fig-fit">
	<img src="Patching3.png" alt="Another error">
	<figcaption>Another error to patch out.</figcaption>
</figure>

<p>Again, we're looking for branches above our message box, using the arrows on the left to find them more easily. If you click the JZ LAB_00409d1e line, it'll highlight the if for the else our message box sits in. You know the drill - we always want to branch here, so change it to a JMP. If you make a mistake you can press Ctrl+Z to undo it.</p>

<p>Now take a look at the code at the cursor. Does it seem odd to you? We seem to be calling a function, and then recursing 
back into WinMain. How weird! I have no idea why it might be doing this, but if you look at the if statement's expression, its 
checking if a HKEY is 0. HKEY is a registry type, so we can probably make an educated guess that the function we're comparing 
it to is a registry key. In this case the assignment is the line above, but you can use the Highlight right click menu to highlight the assignment and subsequent use of this variable. "Def-Use" is the most useful highlight option here, but you should read the docs on this to pick the most appropriate highlight version. You can investigate this code path if you like, but it isn't important. If you look at the else clause it uses the value multiple times going forward, so patching this out means its going to crash if that registry key isn't present.</p>

<p>The question is, do we care? NOPE! Right now we just want to clear a path through and make the code more readable. If you click the if statement though, you'll notice this one is a JNZ this time. Same procedure though, we want to swap it for JMP. Do that now and watch the if statement vanish and the else clause becoming the only code path.<p>

<p>Let's be smarter about our next patch. Instead of going after the next dialog box, lets see if we need to go near any of the code in that branch.</p>

<figure class="fig-noresize">
	<img src="UselessCode.png" alt="Some useless code highlighted">
	<figcaption>None of the highlighted code looks useful, we've got a message box and that weird WinMain recursion again.</figcaption>
</figure>

<p>But what about FUN_004087b2?</p>

<figure class="fig-noresize">
	<img src="CloseKey.png" alt="Code that calls RegCloseKey">
	<figcaption>So this function only calls RegCloseKey. As such we don't actually care.</figcaption>
</figure>

<p>Even though this function isn't relevant to us, we should still rename it as we've identified it. I'm sure that'll be called more than once. Rename it to CloseKey and click the back button or Alt+Left to go back.</p>

<p>So what about the parent if statement? What's RegisteredSysCode? Well, given the name and error message its probably registration/license related. It's also checking if two values are 0, so looks like its just checking if the key exists. Failure seems to be the if case, so we want to always take the else.</p>

<p>Rather than navigate the assembly, click the if keyword and it should highlight the JZ instruction. However, this time we <i>never</i> want to take the branch. Now, I want to show you two ways to do this next bit. The first way would be to just not jump by patching with NOPs. That seems straightforward enough so lets give that a go...</p>


<figure class="fig-fit">
	<img src="PatchingNop1.png" alt="About to patch a NOP">
	<figcaption>Have you spotted the mistake yet?</figcaption>
</figure>

<p>The keen eyed of you may have spotted the mistake already, for those that haven't - how many bytes does the instruction need vs how many do we currently take? Hit enter to confirm.</p>

<figure class="fig-fit">
	<img src="PatchingNop2.png" alt="Disaster strikes!">
	<figcaption>Uh, that doesn't look good...</figcaption>
</figure>

<p>We appear to have a bunch of ??s. Quite a few in fact, as our NOP doesn't take a 4 byte address either. It also says "Bad instruction" in the comments, so it really doesn't like it, and you can tell because the generated code is... odd. So what can we do? MOAR NOPS!</p>

<p>For each ?? replace it with a NOP. To do this quicker, use the keyboard shortcut Ctrl+Shift+G. After completing them all the code will look more sane, but the if is still there! That's because there were actually two JZ instructions making up that if statement. Surprise! Scroll up, find the other one, and patch it with the same process.</p>

<figure class="fig-fit">
	<img src="PatchingNop3.png" alt="Patched">
	<figcaption>When you've patched both it should look like this.</figcaption>
</figure>

<p>Even after fixing those cases you'll notice there's still lots of weird logic and trial expired errors we need to worry about. There must be an easier way, right? Of course there is! To illustrate this, press Ctrl+Z a few times until both the JZ instructions appear again.</p>

<p>This time we're going to be surgical about our approach. Our goal here is to surgically skip all the licensing code with as few changes as possible. To do this we need a location to jump from, and one to jump to.</p>

<p>Click on the line above the if: local_1b0 = FUN_00408750(pHVar4,pcVar7). From the if statement we know this is registry related, so we probably need it. To confirm, click on local_1b0, right click and click Highlight -> Forward Slice. This will show us where we use the value from this point onwards until it gets reassigned. There are uses within the else clause, so yes we definitely need this.</p>

<figure class="fig-fit">
	<img src="IdentifyingJumpPoint.png" alt="Our registry key usage">
	<figcaption>Registry key usage, and where we acquire it.</figcaption>
</figure>

<p>Given we know we need the CALL highlighted, we need the ADD too as this is decrementing the stack by the amount used by the function in the previous CALL. The MOV, CMP and JZ instructions we don't need though. Lets bookmark these instructions for now as this will be the origin of our jump.</p>

<p>Now let's work backwards and find the first instruction after the license check. We found those UI related RegisterWindowMessageA calls earlier, so that seems like a sensible place to start.</p>

<figure class="fig-fit">
	<img src="FindingDestination.png" alt="Our UI code">
	<figcaption>Our UI code setup.</figcaption>
</figure>

<p>At the top there we can see the end of a scope that contains some trial related code. After that we can seem some string manipulation. A quick look and this appears to be a hand rolled string copy, which is odd, but it doesn't really matter because we don't care. There's no early return here, so we can ignore it. As such, we can treat the end of the scope on line 204 as our destination.</p>

<p>Click line 204 and find the label and rename it to MainInit.</p>

<p>The astute among you may have noticed that between the origin and destination there is some code that isn't license related, and that's the Passcode reading code. From what I remember, this was so you could password protect the settings menu. If we're honest, the type of people to run a cracked copy of a program maybe don't care so much about security features like this, so we can get away with just breaking it. Of course, you could put the time in to do it properly and ensure that code gets called though.</p>

<p>For now though, introducing a bug isn't a huge deal, and it would make this already long post even longer, so we won't do it here.</p>

<p>So, lets get on with it! Go back to our previously set Origin bookmark. Patch the MOV command to say JMP MainInit. This will create one bad instruction byte, so patch that with a NOP.</p>

<p>Now comes the fun bit! Go to the File menu and Save As, saving your changes to a new copy. Then go to the Ghidra project window where you dropped the original exe, select the newly saved one, right click and click Export. On that menu change the format to Binary and save it somewhere then press OK. Rename and copy the new file over the top of the original exe in the install directory, making a backup copy of the original in the process. Now, run it!</p>

<figure class="fig-noresize">
	<img src="Splash.png" alt="Splash screen">
	<figcaption>Success! The splash screen in all its Papyrus glory.</figcaption>
</figure>

<h2>But wait, there's more!</h2>

<figure class="fig-noresize">
	<img src="NotRegistered.png" alt="Another registration error">
	<figcaption>Wait, where did that come from?</figcaption>
</figure>

<p>This version of the string omits the 'Was'. Find this string again, this time however there are no matches!</p>

<p>So where <i>is</i> it coming from?</p>

<p>Here the program's own logging helps us out. There are some useful strings in WinMain such as "In WinMain After SplashScreen". Handy! We know we just saw the splash screen, so we're definitely there. The logs also hint at loading Modules\HTTP_Service.dll. Off we go to that! As before, find the DLL and drag it into Ghidra, double click it and run the analysis with default settings.</p>

<p>Same as before, lets search for that new string.</p>

<figure class="fig-noresize">
	<img src="NotRegisteredStrings.png" alt="Our new strings">
	<figcaption>String matches in the DLL</figcaption>
</figure>

<p>One match. Click it, right click the address, find references. One reference!</p>

<figure class="fig-noresize">
	<img src="DllLicenseCheck.png" alt="Our license check">
	<figcaption>Our now very naked license check</figcaption>
</figure>

<p>If we read through this, it looks very similar to last time. We're reading some registry keys, and because this time the program flow is much simpler, we can see the very naked string comparison. The function strcmp returns 0 when the strings match, so that's our success path. You can also see the error message in the else. Now, the key the function initially opens is the root key, so should always exist. So we can ignore that case. We just want to focus on that if check for the string comparison.</p>

<p>Select the if, right click the JZ and change it to a JMP. Now save and export then update the DLL in the modules directory with our modified version. Now, spoiler alert, there's <i>another</i> check, but it's basically the same again. Once you patch that too then it'll finally boot!</p>

<figure class="fig-noresize">
	<img src="ActualCheck.png" alt="🤦‍♂">
	<figcaption>The actual code for comparison. 🤦‍♂</figcaption>
</figure>

<p>If you were wondering why there appears to be multiple memsets of the same variable multiple times one after another, they're in the code too! I'm willing to bet I thought this was more secure somehow? Hint... it's not!</p>

<p>At this point the server starts and all the features are unlocked. Our work here is done, but we've got one loose end to tie up first.</p>

<h2>Admin-only dialog</h2>

<p>Rewinding right back to the beginning, do you remember that Administrator only dialog box? It was a dialog box rather than a message box that said "The license currently present has expired or is invalid." The changes we made above will also prevent this dialog from showing, so if you want to follow along its best to do so on a clean copy of the exe.</p>

<figure class="fig-noresize">
	<img src="TheLicenseMessage.png" alt="A search box showing no results.">
	<figcaption>If you search for "The license" using the Memory window, you get no hits.</figcaption>
</figure>

<p>Curiously, if you search for a substring of this, you'll get no hits by default. This is actually because this is one of the few strings stored in Unicode. I assume that's a quirk of dialog resources, I don't really know. However, armed with this knowledge, you can now change the Encoding box to UTF-16, and you'll get a hit.</p>

<p>If you then try to find references to that string like you have in the past, you'll also get no hits because it's a resource string, not a C string used directly in code.</p>

<figure class="fig-noresize">
	<img src="ResourceListing.png" alt="A snip of the resource listing.">
	<figcaption>A snippet of the resource listing - trimmed for brevity.</figcaption>
</figure>

<p>If you look at the top you'll see a line labelled Rsrc_Dialog_8d_809. This is the name that Ghidra gave to the address immediately below it, and this can be used to find references to the dialog box. If you do this, you'll get one match.</p>

<figure class="fig-noresize">
	<img src="LicenseDialog.png" alt="Code referencing the dialog resource.">
	<figcaption>The code referencing the dialog resource. Ghidra managed to find the use even though the template is referencing a value 0x8d. Neat!</figcaption>
</figure>

<p>You can poke around the functions this calls if you want, but they're not really important - we just want to see what might cause it to get shown, so we want to find references to this function (FUN_00408870). There are actually 6 references to this function! If you do a quick scan over them, you'll notice the pattern is always the same:</p>

<figure class="fig-noresize">
	<img src="ShowLicenseDialogFragment.png" alt="The repeating fragment of code to show the license dialog.">
	<figcaption>It looks like we're doing some registry operations again, and the dialog shows if it can't be read </figcaption>
</figure>

<p>DAT_0041c450 gets set to 1 if -SERVICE is set on the command line, so presumably 0 means it is in application mode. This makes sense given if you're running in service mode then you can't display dialog boxes - but I didn't think you could display message boxes either... Anyway, we can only get into that branch if reading the registry values fails.</p>

<p>So the question is, why would a registry read fail as a user, but not as Administrator? If you're familiar with Win32, you may have already figured this out. First, lets look at what that top function is doing. First, we're casting a rather unique value to a HKEY, so this must be some kind of special handle value.</p>

<p>If you search the internet with your favourite search engine and 'hkey', you'll get a load of hits for that value:</p>

<figure class="fig-noresize">
	<img src="HKeySearch.png" alt="Search results for 0x80000002 hkey">
	<figcaption>Search results for 0x80000002 hkey</figcaption>
</figure>

<p>So we now know this key is HKEY_LOCAL_MACHINE. I have a pretty good hunch I know what the problem is here, but let's confirm it by looking inside that function.</p>

<figure class="fig-noresize">
	<img src="RegFunction.png" alt="Ghidra helping us with function declarations">
	<figcaption>Ghidra has helpfully given us the declaration of this function.</figcaption>
</figure>

<p>There's an interesting value of 0xf003f for the samDesired parameter. That's the access permissions. If you look up RegCreateKeyEx and follow the docs for that parameter, that value is assigned to <b>KEY_ALL_ACCESS</b>. That means we're trying to acquire write permissions to HKEY_LOCAL_MACHINE - it looks like this is a one-size-fits-all registry wrapper, and it's asking for write permissions when it is only reading. Write permissions to HKEY_LOCAL_MACHINE got restricted in Windows at some point, so this is a compatibility issue. The fact that the rest of it works is a testament to how well Windows handles back-compat!</p>

<h2>Resources</h2>

<a href="icTransfer.exe">You can download icTransfer here</a> if you wish to follow along at home. <p><b>NOTE</b> This software is likely extremely vulnerable and should not be exposed to the internet.</p>

<?php include '../../components/blog_post_footer.php' ?>