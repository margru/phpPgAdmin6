<?php

	/**
	 * List tables in a database
	 *
	 * $Id: tblproperties.php,v 1.5 2003/03/10 02:15:14 chriskl Exp $
	 */

	// Include application functions
	include_once('libraries/lib.inc.php');

	$action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
	$PHP_SELF = $_SERVER['PHP_SELF'];

	function doExport($msg = '') {
		global $data, $localData, $misc;
		global $PHP_SELF, $strExport, $strReset;

		$misc->printTableNav();
		echo "<h2>", htmlspecialchars($_REQUEST['database']), ": ", htmlspecialchars($_REQUEST['table']), ": {$strExport}</h2>\n";
		$misc->printMsg($msg);

		echo "<form action=\"tblexport.php\" method=\"post\">\n";
		echo "<table>\n";
		echo "<tr><th class=data>Format:</th><td><select name=\"format\">\n";
		echo "<option value=\"sql\">SQL</option>\n";
		echo "<option value=\"csv\">CSV</option>\n";
		echo "<option value=\"tab\">Tabbed</option>\n";
		echo "<option value=\"xml\">XML</option>\n";
		echo "</select></td></tr>";
		/*echo "<tr><th class=data>Content:</th><td><select name=\"content\">\n";
		echo "<option value=\"all\">Schema &amp; Data</option>\n";
		echo "<option value=\"schema\">Schema Only</option>\n";
		echo "<option value=\"data\">Data Only</option>\n";
		echo "</select></td></tr>";*/
		echo "<tr><th class=data>OIDS:</th><td><input type=\"checkbox\" name=\"oids\"></td></tr>";
		echo "<tr><th class=data>Download?</th><td><input type=\"checkbox\" name=\"download\"></td></tr>";
		echo "</table>\n";

		echo "<p><input type=hidden name=action value=export>\n";
		echo $misc->form;
		echo "<input type=hidden name=table value=\"", htmlspecialchars($_REQUEST['table']), "\">\n";
		echo "<input type=submit value=\"{$strExport}\"> <input type=reset value=\"{$strReset}\"></p>\n";
		echo "</form>\n";
	}

	/**
	 * Displays a screen where they can add a column
	 */
	function doAddColumn($msg = '') {
		global $data, $localData, $misc;
		global $PHP_SELF, $strShowAllTables, $strInvalidScriptParam;

		if (!isset($_REQUEST['stage'])) $_REQUEST['stage'] = 1;

		switch ($_REQUEST['stage']) {
			case 1:
				global $strField, $strType, $strAdd, $strReset;
				global $strLength, $strAddColumn;

				// Set variable defaults
				if (!isset($_POST['field'])) $_POST['field'] = '';
				if (!isset($_POST['type'])) $_POST['type'] = '';
				if (!isset($_POST['length'])) $_POST['length'] = '';

				// Fetch all available types
				$types = &$localData->getTypes(true);

				$misc->printTableNav();
				echo "<h2>", htmlspecialchars($_REQUEST['database']), ": ",
					htmlspecialchars($_REQUEST['table']), ": {$strAddColumn}</h2>\n";
				$misc->printMsg($msg);

				echo "<form action=\"$PHP_SELF\" method=\"post\">\n";

				// Output table header
				echo "<table>\n<tr>";
				echo "<tr><th class=\"data\">{$strField}</th><th class=\"data\">{$strType}</th><th class=\"data\">{$strLength}</th></tr>";

				echo "<tr><td><input name=\"field\" size=\"32\" maxlength=\"{$data->_maxNameLen}\" value=\"",
					htmlspecialchars($_POST['field']), "\"></td>";
				echo "<td><select name=\"type\">\n";
				while (!$types->EOF) {
					$typname = $types->f[$data->typFields['typname']];
					echo "<option value=\"", htmlspecialchars($typname), "\"", ($typname == $_POST['type']) ? ' selected' : '', ">",
						htmlspecialchars($typname), "</option>\n";
					$types->moveNext();
				}
				echo "</select></td>\n";
				echo "<td><input name=\"length\" size=\"8\" value=\"",
					htmlspecialchars($_POST['length']), "\"></td></tr>";
				echo "</table>\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"add_column\">\n";
				echo "<input type=\"hidden\" name=\"stage\" value=\"2\">\n";
				echo $misc->form;
				echo "<input type=\"hidden\" name=\"table\" value=\"", htmlspecialchars($_REQUEST['table']), "\">\n";
				echo "<p><input type=\"submit\" value=\"{$strAdd}\"> <input type=\"reset\" value=\"{$strReset}\"></p>\n";
				echo "</form>\n";

				break;
			case 2:
				global $localData, $strFieldNeedsName, $strColumnAdded, $strColumnAddedBad;

				// Check inputs
				if (trim($_POST['field']) == '') {
					$_REQUEST['stage'] = 1;
					doAddColumn($strFieldNeedsName);
					return;
				}
				
				$status = $localData->addColumn($_POST['table'], $_POST['field'],
								$_POST['type'], $_POST['length']);
				if ($status == 0)
					doDefault($strColumnAdded);
				else {
					$_REQUEST['stage'] = 1;
					doAddColumn($strColumnAddedBad);
					return;
				}
				break;
			default:
				echo "<p>{$strInvalidScriptParam}</p>\n";
		}
	}

	/**
	 * Displays a screen where they can alter a column
	 */
	function doProperties($msg = '') {
		global $data, $localData, $misc;
		global $PHP_SELF, $strTables, $strShowAllTables;

		if (!isset($_REQUEST['stage'])) $_REQUEST['stage'] = 1;

		switch ($_REQUEST['stage']) {
			case 1:
				global $strField, $strType, $strNotNull, $strDefault, $strAlter, $strReset, $strAlterColumn;

				$misc->printTableNav();
				echo "<h2>", htmlspecialchars($_REQUEST['database']), ": {$strTables}: {$strAlterColumn}: ",
					htmlspecialchars($_REQUEST['column']), "</h2>\n";
				$misc->printMsg($msg);

				echo "<form action=\"$PHP_SELF\" method=\"post\">\n";

				// Output table header
				echo "<table>\n<tr>";
				echo "<tr><th class=data>{$strField}</th><th class=data>{$strType}</th><th class=data>{$strNotNull}</th><th class=data>{$strDefault}</th></tr>";

				$column = &$localData->getTableAttributes($_REQUEST['table'], $_REQUEST['column']);
				$column->f['attnotnull'] = $localData->phpBool($column->f['attnotnull']);

				if (!isset($_REQUEST['default'])) {
					$_REQUEST['field'] = $column->f['attname'];
					$_REQUEST['default'] = $_REQUEST['olddefault'] = $column->f['adsrc'];
					if ($column->f['attnotnull']) $_REQUEST['notnull'] = 'YES';
				}				

				echo "<tr><td><input name=\"field\" size=\"32\" value=\"",
					htmlspecialchars($_REQUEST['field']), "\"></td>";
				echo "<td>", htmlspecialchars($column->f['type']), "</td>";
				echo "<td><input type=checkbox name=\"notnull\"", (isset($_REQUEST['notnull'])) ? ' checked' : '', "></td>\n";
				echo "<td><input name=\"default\" size=20 value=\"", 
					htmlspecialchars($_REQUEST['default']), "\"></td>";
				
				echo "</table>\n";
				echo "<input type=hidden name=action value=properties>\n";
				echo "<input type=hidden name=stage value=2>\n";
				echo $misc->form;
				echo "<input type=hidden name=table value=\"", htmlspecialchars($_REQUEST['table']), "\">\n";
				echo "<input type=hidden name=column value=\"", htmlspecialchars($_REQUEST['column']), "\">\n";
				echo "<input type=hidden name=olddefault value=\"", htmlspecialchars($_REQUEST['olddefault']), "\">\n";
				echo "<input type=submit value=\"{$strAlter}\"> <input type=reset value=\"{$strReset}\">\n";
				echo "</form>\n";
								
				break;
			case 2:
				global $localData, $strFieldNeedsName, $strColumnAltered, $strColumnAlteredBad, $strInvalidScriptParam;

				// Check inputs
				if (trim($_REQUEST['field']) == '') {
					$_REQUEST['stage'] = 1;
					doProperties($strFieldNeedsName);
					return;
				}
				
				$status = $localData->alterColumn($_REQUEST['table'], $_REQUEST['column'], $_REQUEST['field'], 
								isset($_REQUEST['notnull']), $_REQUEST['default'], $_REQUEST['olddefault']);
				if ($status == 0)
					doDefault($strColumnAltered);
				else {
					$_REQUEST['stage'] = 1;
					doProperties($strColumnAlteredBad);
					return;
				}
				break;
			default:
				echo "<p>{$strInvalidScriptParam}</p>\n";
		}
	}

	/**
	 * Show confirmation of drop column and perform actual drop
	 */
	function doDrop($confirm) {
		global $localData, $database, $misc;
		global $PHP_SELF, $strTables, $strDrop, $strConfDropColumn, $strYes, $strNo, $strColumnDropped, $strColumnDroppedBad;

		if ($confirm) {
			echo "<h2>", htmlspecialchars($_REQUEST['database']), ": {$strTables}: ", 
				htmlspecialchars($_REQUEST['table']), ": " , htmlspecialchars($_REQUEST['column']), ": {$strDrop}</h2>\n";

                        echo "<p>", sprintf($strConfDropColumn, htmlspecialchars($_REQUEST['column']),
                                htmlspecialchars($_REQUEST['table'])), "</p>\n";
								

			echo "<form action=\"$PHP_SELF\" method=\"post\">\n";
			echo "<input type=hidden name=action value=drop>\n";
			echo "<input type=hidden name=table value=\"", htmlspecialchars($_REQUEST['table']), "\">\n";
			echo "<input type=hidden name=column value=\"", htmlspecialchars($_REQUEST['column']), "\">\n";
			echo $misc->form;
			echo "<input type=submit name=choice value=\"{$strYes}\"> <input type=submit name=choice value=\"{$strNo}\">\n";
			echo "</form>\n";
		}
		else {
			$status = $localData->dropColumn($_POST['table'], $_POST['column'], 'RESTRICT');
			if ($status == 0)
				doDefault($strColumnDropped);
			else
				doDefault($strColumnDroppedBad);
		}
		
	}

	/**
	 * Show default list of tables in the database
	 */
	function doDefault($msg = '') {
		global $data, $localData, $misc;
		global $PHP_SELF, $strTable, $strOwner, $strActions, $strNoTable;
		global $strBrowse, $strProperties, $strDrop, $strShowAllTables;
		global $strKeyName, $strUnique, $strField, $strType, $strNotNull, $strDefault, $strAction, $strPrimary;
		global $strSelect, $strInsert, $strAddColumn, $strDrop;

		$misc->printTableNav();
		echo "<h2>", htmlspecialchars($_REQUEST['database']), ": ", htmlspecialchars($_REQUEST['table']), "</h2>\n";

		$attrs = &$localData->getTableAttributes($_REQUEST['table']);

		if ($attrs->recordCount() > 0) {
			echo "<table>\n";
			echo "<tr><th class=data>{$strField}</th><th class=data>{$strType}</th><th class=data>{$strNotNull}</th><th class=data>{$strDefault}</th><th colspan=2 class=data>{$strActions}</th>\n";
			$i = 0;
			while (!$attrs->EOF) {
				$attrs->f['attnotnull'] = $localData->phpBool($attrs->f['attnotnull']);
				$id = (($i % 2) == 0 ? '1' : '2');
				echo "<tr><td class=data{$id}>", htmlspecialchars($attrs->f['attname']), "</td>\n";
				echo "<td class=data{$id}>", htmlspecialchars($attrs->f['type']), "</td>\n";
				echo "<td class=data{$id}>", ($attrs->f['attnotnull'] ? 'NOT NULL' : ''), "</td>\n";
				echo "<td class=data{$id}>", htmlspecialchars($attrs->f['adsrc']), "</td>\n";
				echo "<td class=opbutton{$id}><a href=\"{$PHP_SELF}?{$misc->href}&table=", htmlspecialchars($_REQUEST['table']),
					"&column=", htmlspecialchars($attrs->f['attname']), "&action=properties\">{$strProperties}</a></td>\n";
				echo "<td class=opbutton{$id}><a href=\"{$PHP_SELF}?{$misc->href}&table=", htmlspecialchars($_REQUEST['table']),
					"&column=", htmlspecialchars($attrs->f['attname']), "&action=confirm_drop\">{$strDrop}</a></td>\n";
				echo "</tr>\n";
				$attrs->moveNext();
				$i++;
			}
			echo "</table>\n";
			echo "<br />\n";

			echo "<ul>\n";
			echo "<li><a href=\"tables.php?action=browse&page=1&{$misc->href}&table=", urlencode($_REQUEST['table']),"\">{$strBrowse}</a></li>\n";
			echo "<li><a href=\"tables.php?action=confselectrows&{$misc->href}&table=", urlencode($_REQUEST['table']),"\">{$strSelect}</a></li>\n";
			echo "<li><a href=\"tables.php?action=confinsertrow&{$misc->href}&table=", urlencode($_REQUEST['table']),"\">{$strInsert}</a></li>\n";
			echo "<li><a href=\"tables.php?action=confirm_drop&{$misc->href}&table=", urlencode($_REQUEST['table']),"\">{$strDrop}</a></li>\n";
			echo "<li><a href=\"{$PHP_SELF}?action=add_column&{$misc->href}&table=", urlencode($_REQUEST['table']),"\">{$strAddColumn}</a></li>\n";
			echo "</ul>\n";
		}
		else {
			echo "<p>{$strNoTable}</p>\n";
		}
	}

	$misc->printHeader($strTables . ' - ' . $_REQUEST['table']);
	$misc->printBody();
	
	switch ($action) {
		case 'export':
			doExport();
			break;
		case 'add_column':
			doAddColumn();
			break;
		case 'properties':
			doProperties();
			break;
		case 'drop':
			if ($_POST['choice'] == "{$strYes}") doDrop(false);
			else doDefault();
			break;
		case 'confirm_drop':
			doDrop(true);
			break;
		default:
			doDefault();
			break;
	}
	
	$misc->printFooter();

?>