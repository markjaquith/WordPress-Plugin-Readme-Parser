<?php
require('parse-readme.php');

function validate_readme($r) {

	$warnings = array();
	$fatal_errors = array();
	$notes = array();

	// fatal errors
	if ( !$r['name'] )
		$fatal_errors[] = 'No plugin name detected.  Plugin names look like: <code>=== Plugin Name ===</code>';

	// warnings
	if ( !$r['requires_at_least'] )
		$warnings[] = '<code>Requires at least</code> is missing';
	if ( !$r['tested_up_to'] )
		$warnings[] = '<code>Tested up to</code> is missing';
	if ( !$r['stable_tag'] )
		$warnings[] = '<code>Stable tag</code> is missing.  Hint: If you treat <code>/trunk/</code> as stable, put <code>Stable tag: trunk</code>';
	if ( !count($r['contributors']) )
		$warnings[] = 'No <code>Contributors</code> listed';
	if ( !count($r['tags']) )
		$warnings[] = 'No <code>Tags</code> specified';
	if ( $r['is_excerpt'] )
		$warnings[] = 'No <code>== Description ==</code> section was found... your short description section will be used instead';
	if ( $r['is_truncated'] )
		$warnings[] = 'Your short description exceeds the 150 character limit';


	// notes
	if ( !$r['sections']['installation'] )
		$notes[] = 'No <code>== Installation ==</code> section was found';
	if ( !$r['sections']['frequently_asked_questions'] )
		$notes[] = 'No <code>== Frequently Asked Questions ==</code> section was found';
	if ( !$r['sections']['changelog'] )
		$notes[] = 'No <code>== Changelog ==</code> section was found';
	if ( !$r['sections']['screenshots'] )
		$notes[] = 'No <code>== Screenshots ==</code> section was found';
	if ( !$r['donate_link'] )
		$notes[] = 'No donate link was found';

	readme_validator_head('Readme Validator Results');

	// print those errors, warnings, and notes
	if ( $fatal_errors ) {
		echo "<div class='fatal error'><p>Fatal Error:</p>\n<ul class='fatal error'>\n";
		foreach ( $fatal_errors as $e )
			echo "<li>$e</li>\n";
		echo "</ul>\n</div>";
		return; // no point staying
	}

	if ( $warnings ) {
		echo "<div class='warning error'><p>Warnings:</p>\n<ul class='warning error'>\n";
		foreach ( $warnings as $e )
			echo "<li>$e</li>\n";
		echo "</ul>\n</div>";
	}

	if ( $notes ) {
		echo "<div class='note error'><p>Notes:</p>\n<ul class='note error'>\n";
		foreach ( $notes as $e )
			echo "<li>$e</li>\n";
		echo "</ul>\n</div>";
	}

	if ( !$notes && !$warnings && !$fatal_errors )
		echo "<div class='success'><p>Your <code>readme.txt</code> rocks.  Seriously.  Flying colors.</p></div>\n";
	else
		echo "<a href='#re-edit'>Re-Edit your Readme File</a>\n";

	// Show the data, as interpreted
	?>
	<hr />

	<h1><?php echo $r['name']; ?></h1>

	<p><em><?php echo $r['short_description']; ?></em></p>

	<hr />

	<p>
	<strong>Contributors:</strong> <?php echo implode(', ', $r['contributors']); ?><br />
	<strong>Donate link:</strong> <?php echo $r['donate_link']; ?><br />
	<strong>Tags:</strong> <?php echo implode(', ', $r['tags']);?><br />
	<strong>Requires at least:</strong> <?php echo $r['requires_at_least']; ?><br />
	<strong>Tested up to:</strong> <?php echo $r['tested_up_to']; ?><br />
	<strong>Stable tag:</strong> <?php echo $r['stable_tag']; ?>
	</p>

	<hr />

	<?php foreach ( $r['sections'] as $title => $section ) : ?>
	<h3><?php echo ucwords(str_replace('_', ' ', $title)); ?></h3>
	<?php echo $section; ?>
	<hr />
	<?php endforeach; ?>

	<?php echo $r['remaining_content'];
	echo "\n<hr />\n\n";
	echo "<h2 id='re-edit'>Re-Edit your Readme File</h2>\n";
}


function readme_validator_head($title) { ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?php echo $title; ?></title>
</head>
<style type="text/css">
<!--
body {
font-family: Lucida Grande, Verdana, sans-serif;
}

code {
font-size: 1.3em
}

div.success {
background: #0f0;
width: 50%;
margin: 0 auto;
padding: 1px 10px;
border: 3px solid #0d0;
}

div.error {
padding: 1px 10px;
margin: 30px auto;
}

div.error p {
font-weight: bold;
}

div.error ul {
list-style: square;
}

div.fatal {
background: #faa;
border: 3px solid #d00;
}

div.warning {
background: #f60;
border: 3px solid #e40;
}

div.note {
background: #5cf;
border: 3px solid #3ad;
}

-->
</style>
<body>
<?php
}

$readme_contents = '';
$url = '';
if ( $_POST['url'] ) {
	$url = wp_kses_no_null( stripslashes($_POST['readme_url']) );
	$url = clean_url( $url );

	if ( strtolower(substr($url, -10, 10)) != 'readme.txt') {
		readme_validator_head('Validator Error!');
		die('URL must end in <code>readme.txt</code>!');
	}

	if ( !$readme_contents = file_get_contents($url) ) {
		readme_validator_head('Validator Error!');
		die('Invalid readme.txt URL');
	}

	$r = new Automattic_Readme;
	$readme = $r->parse_readme_contents($readme_contents);
	validate_readme($readme);
} elseif ( $_POST['text'] ) {
	$readme_contents = stripslashes($_POST['readme_contents']);

	$r = new Automattic_Readme;
	$readme = $r->parse_readme_contents($readme_contents);
	validate_readme($readme);
} else {
	readme_validator_head('WordPress/BBPress Plugin readme.txt Validator');
?>
<p>Enter the URL to your <code>readme.txt</code>:</p>
<form method="post" action="">
	<input type="hidden" name="url" value="1" />
	<p>http://<input type="text" name="readme_url" size="70" /> <input type="submit" value="Validate!" /></p>
</form>

<p>... or paste your <code>readme.txt</code> here:</p>
<?php

}
?>

<form method="post" action="">
	<input type="hidden" name="text" value="1" />
	<textarea rows="20" cols="100" name="readme_contents" /><?php echo wp_specialchars( $readme_contents ); ?></textarea>
	<p><input type="submit" value="Validate!" /></p>
</form>

</body>
</html>
