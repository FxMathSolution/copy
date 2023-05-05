<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="en" class="ie6 ielt8"> <![endif]-->
<!--[if IE 7 ]>    <html lang="en" class="ie7 ielt8"> <![endif]-->
<!--[if IE 8 ]>    <html lang="en" class="ie8"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html lang="en"> <!--<![endif]-->
<head>
<meta charset="utf-8">
<title>Signal Provider Copier</title>
<link rel="stylesheet" type="text/css" href="resources/css/login.css" />
</head>
<body>
<div class="container">
	<section id="content">
		<form action="login_verify.php" method='post'>
			<h1>Login Form</h1>
			<div>
				<input type="text" placeholder="User Name" required="" name="username" id="username" value="<?php if (isset($_GET['username'])) echo $_GET['username']; ?>" />
			</div>
			<div>
				<input type="password" placeholder="Password" required="" name="password" id="password" />
				<?php if (isset($_GET['username'])) echo "<font color='#ff0000'>invalid username/pass combination, please try again</font>"; ?>
			</div>
			<div>
				<input type="submit" name="submit" value="Log in" />
				<a href="#">Lost your password?</a>
				<a href="#">Register</a>
			</div>
		</form><!-- form -->
	</section><!-- content -->
</div><!-- container -->
</body>
</html>
