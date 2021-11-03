<?php
	$banner_image = "team_work_monochromatic.svg";
	$banner_classes = "banner-fit pad-top";
	$banner_alt = "A team constructs a puzzle from pieces.";
	$publish_date = "2021/11/03";
	$post_title = "Software as a Disservice";
	$synopsis = "The rate of code churn in the software industry is hurting us as engineers and our customers. Is there a way to break the cycle?";
	$image_credits = [
		"Team work monochromatic" => ["Many Pixels", "https://www.manypixels.co/" ]
	];
	
	if(isset($is_header)){ return; }
	
	include '../../components/blog_post_header.php'
?>

<p><i>This is an opinion piece and does not necessarily reflect the position held by my employer.</i></p>

<p>Embarc is a software product by Embarco that launched to much fanfare, filling a much-needed niche in its space. After some teething issues around monetizing their product, Embarco became profitable and the de facto market leader. Growing to be a billion-dollar company, Embarco underwent explosive growth over many years and the company grew from 100 employees to 15,000 in just 7 years.</p>

<p>Initially the team focused on implementing features that customers were clamouring for, but they soon ran out of must-have features to develop, instead focusing on internal suggestions and direction from management. Some of these features were received very well and gave the company additional unique selling points for their product, bringing in new users. But overall, users were still using the same core features the product launched with.</p>

<p>At this point you’re probably wondering what Embarc is, and why you’ve never heard of it. Did it end in a ball of flames? Bear with me for now and all will soon be revealed! To accelerate development Embarco switched from a native application to a web-based UI to make iteration faster. On the backend they began migrating to microservices. The company began changing things rather than adding things and their user experience (UX) team went through several iterations of their user interface in just a few years. Most of these changes focused on making it more visually interesting rather than functional, and less used features were pushed off into side menus or removed entirely because they didn’t fit the new aesthetic.</p>

<p>Users found they could no longer do things that brought them to Embarc in the first place. Embarc got progressively slower and laggier as the total volume of code increased.<p/>

<p>Users began to get unhappy, looking to competitors, but found that the market was so dominated by Embarc that competitors weren’t really keeping up, their friends used Embarc, and the other apps were getting slower and buggier too. To address user churn, Embarco began hiring more developers. The team was no longer able to iterate quickly. The codebase was so large that making small changes was slow and had huge implications. The developers that had started the company were now long gone, moving on to new challenges in smaller, more agile teams. The original, simple product was now a monster that now needed a large company to maintain it.</p>

<p>Within the team there was a growing desire to start from scratch. To learn from previous mistakes and start with the right frameworks. It started as a small, focused team with the intent of bringing up the core feature set then migrating the team slowly, eventually they’d replace the old app with the new one when feature parity was reached.</p>

<p>The new version began to take on the same traits as the old app. Their newly adopted framework introduced its own problems, and as the size of the v2 team increased, the bad habits of the old app team were replicated into the new one. Eventually a line was drawn, and the new app was launched. Customers hated it. It was slower and buggier than the old one and users couldn’t understand the changes made.</p>

<p>Customers began to complain that they want the old UI back, but the only way is forward. Occasionally the customers win a small concession, but Embarc of 2020 is objectively worse than the 2015 product in all the ways their core demographic care about.</p>

<p>You’ve probably been trying to work out which company Embarco is. It’s all of them, well, almost all of them.</p>

<h2>The cycle of mediocrity</h2>

<figure class="fig-noresize">
	<img src="cycle.png" alt="Company growth leads to more engineers. More engineers produce more features and more bugs. Teams need to continually justify their existence so push for further growth.">
	<figcaption>Companies can't shrink, they just produce larger and larger codebases, and with that comes complexity.</figcaption>
</figure>

<p>Simply put, as a product becomes more successful, the teams that built it naturally grows. At a certain point those teams “finish” the product, and they begin looking for ways to improve it, because the team exists. To justify their paycheque. A shrinking or stagnant team is a sign of failure, so the team must stay busy to avoid being downsized. The additional code churn begins having negative effects on the product:</p>

<ul>
<li>UI changes frequently alienating customers</li>
<li>The software consumes more resources necessitating new devices to run them</li>
<li>The software takes dependencies on updated libraries and versions of the OS leaving behind old OS versions</li>
<li>The software gets slower and harder to use</li>
</ul>

<p></p>

<p>Think about all the apps you use daily. Are the new features in the apps you use beneficial to you? Do you know what they changed? Did you like the redesign of their user interface? Is the app better or worse than it was 2 years ago?</p>

<p>I bet the answer to all those questions for almost all apps is No.</p>

<h2>Breaking the loop</h2>

<p>There’s no malice or ill will for any parties involved here. Every step in the chain is logical. Every feature and change justifiable. It’s only when you look at the bigger picture and the wider trends, that you see the damage we as developers have been doing to ourselves, to our customers, and to the environment.</p>

<p>The normal answer to this kind of problem is that “the free market will decide”. In theory customers are free to switch to better products at any time, but this ignores a few factors:</p>

<ul>
<li>That there is a competitor to switch to. They may not have the desired content (with content locked behind long exclusivity deals). They may not have a specific feature that’s essential for that customer. It may just be too much effort to switch, or their data is locked inside the app.</li>
<li>Software churn causes hardware churn and this is bad for the environment. People throw devices away that would normally be perfectly functional if not for the fact that apps are running slower than before.</li>
<li>Software changes leave behind laggards and those that struggle with change. I’m thinking about your grandparents, your aunt who doesn’t get these “computer” things, and those that just don’t like change. User interface churn causes confusion, breaks workflows that worked perfectly fine before, and increase support calls to companies and family members.</li>
<li>The amount of CPU time spent running inefficient software is costing us real money and damaging the planet in the process – all for a worse experience.</li>
<li>All the apps are undergoing the same churn, so few products can stay ahead of this curve for long.</li>
</ul>

<p></p>

<p>So, how do we fix this? I don’t think there’s a simple solution here that’ll work everywhere, nor do I think some apps or companies can even be “saved”.</p>

<ul>
<li>Stop treating company growth as a strength. Small teams can happily maintain small apps, especially in the age of the cloud. As a society we need to enter a new age of sustainability, not clinging to failed economic models that require infinite growth to work.</li>
<li>Branching out into new spaces is great, but where possible make it a new app. Keep your apps small and focused on something specific. If the app doesn’t catch on, you’ve no longer got vestigial code acting like a tumour in your single large app when the product is shut down.</li>
<li>“Modern” app development is honestly, a <i>fucking mess</i>. I avoid anything that touches or goes near systems like npm. There are a great many jokes about the sizes of the node_modules folder, and for good reason. With it came build problems, dependency problems, security problems and workflow problems. Entire ecosystems of tools to solve the problems caused by other packages, or just the quantity of them. This is not a healthy ecosystem, and we have an entire generation of developers that believe this is what software development is. It <i>isn’t</i>.</li>
<li>When a company stagnates, vote with your feet – both as an engineer and as a customer. Push back against features that don’t add customer value. Establish if a feature is wanted before implementing it.</li>
<li>Apply the YAGNI principle. You aren’t gonna need it.</li>
</ul>

<p></p>

<p>I don’t have all the answers here, but I believe as an industry we’re getting a bad name for ourselves and alienating our customers. How this will end I don’t know, but I know as an early adopter this is annoying me, and I can tolerate a lot more than the average consumer can. I call this trend “<i>software as a disservice</i>”. And this makes me SaaD.</p>

<h2>Further reading</h2>

<p>If you liked this, you might like to check out:

<ul>
<li><a href="https://blog.pragmaticengineer.com/uber-app-rewrite-yolo/">Uber's Crazy YOLO App Rewrite, From the Front Seat - The Pragmatic Engineer</a></li>
<li><a href="https://www.infoworld.com/article/3639050/complexity-is-killing-software-developers.html">Complexity is killing software developers</a></li>
<li><a href="https://betterprogramming.pub/yagni-you-aint-gonna-need-it-f9a178cd8e1">YAGNI: You Ain’t Gonna Need It</a></li>
</ul>

<small><i>Embarc and Embarco are fictional names for the purposes of illustration and similarities or overlaps with real entities are coincidental.</i></small>

<?php include '../../components/blog_post_footer.php' ?>