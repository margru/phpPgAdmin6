<?php

	/**
	 * Login screen
	 *
	 * $Id: login.php,v 1.6 2003/03/10 02:15:14 chriskl Exp $
	 */

	// This needs to be an include once to prevent lib.inc.php infifite recursive includes
	// Check to see if the configuration file exists, if not, explain
	include_once('libraries/lib.inc.php');

	// Unfortunately, since sometimes lib.inc.php has been included, but we still
	// need the config variables
	if (file_exists('conf/config.inc.php')) {
		require('conf/config.inc.php');
	}
	else {
		echo "Configuration Error: You must rename/copy config.inc.php-dist to config.inc.php and set your appropriate settings";
		exit;
	}

	// Prepare form variables
	if (!isset($_POST['formServer'])) $_POST['formServer'] = '';
	if (!isset($_POST['formLanguage'])) $_POST['formLanguage'] = $appDefaultLanguage;

	// Output header
	$misc->printHeader($strLogin);
	$misc->printBody();
?>

	<table class="navbar" border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
		<tr height="115">
			<td height="115" align="center" valign="middle">
				<center>
				<h1><?php echo $appName ?> <?php echo $appVersion ?> <?php echo $strLogin ?></h1>
				<?php if (isset($_failed) && $_failed) echo "<p class=\"message\">$strLoginFailed</p>" ?>
				<table class="navbar" border="0" cellpadding="5" cellspacing="3">
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" name="login_form">
					<tr>
						<td><?php echo $strUsername ?>:</td>
						<td><input type="text" name="formUsername" value="<?php echo (isset($_POST['formUsername'])) ? htmlspecialchars($_POST['formUsername']) : '' ?>" size="24"></td>
					</tr>
					<tr>
						<td><?php echo $strPassword ?>:</td>
						<td><input type="password" name="formPassword" size="24"></td>
					</tr>
					<tr>
						<td><?php echo $strServer ?>:</td>
						<td><select name="formServer">
						<?php
							for ($i = 0; $i < sizeof($confServers); $i++) {
								echo "<option value=\"{$i}\"",
									($i == $_POST['formServer']) ? ' selected' : '',
									">", htmlspecialchars($confServers[$i]['desc']), "</option>\n";
							}
						?>
						</select></td>
					</tr>
					<tr>
						<td><?php echo $strLanguage ?>:</td>
						<td><select name="formLanguage">
						<?php
							// Language name already encoded
							foreach ($appLangFiles as $k => $v) {
								echo "<option value=\"{$k}\"",
									($k == $_POST['formLanguage']) ? ' selected' : '',
									">{$v}</option>\n";
							}
						?>
						</select></td>
					</tr>
					<tr>
						<td colspan="2" align="right" valign="middle">
							<input type="submit" name="submitLogin" value="Login">
						</td>
					</tr>
					</form>
				</table>
				</center>
				<script language="javascript">
				<!--
					var uname = document.login_form.formUsername;
					var pword = document.login_form.formPassword;
					if (uname.value == "") {
						uname.focus();
					} else {
						pword.focus();
					}
				-->
				</script>
			</td>
		</tr>
	</table>
    </td>
  </tr>
</table>
<?php
	// Output footer
	$misc->printFooter();
?>