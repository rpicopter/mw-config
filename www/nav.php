<?php
echo '
<nav class="navbar navbar-inverse navbar-fixed-top">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
		</div>
		<div id="navbar" class="collapse navbar-collapse">
			<ul class="nav navbar-nav">
				<li id="'.$status_page.'"><a href="'.$status_page.'">'.$status_name.'</a></li>
				<li id="'.$programmer_page.'"><a href="'.$programmer_page.'">'.$programmer_name.'</a></li>	
				<li id="'.$help_page.'"><a href="'.$help_page.'">'.$help_name.'</a></li>	
			</ul>
		</div>
	</div>
</nav>';
?>