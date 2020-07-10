<?php
// Source: https://github.com/wahidulislamriyad/SQLite3-CRUD
// create database 'UserData.db';
//
// use users;
// CREATE TABLE `users` (
//   `id` int NOT NULL,
//   `username` TEXT NOT NULL,
//   `password` TEXT NOT NULL,
//   `name` TEXT NOT NULL,
//   `email` TEXT NOT NULL,
//   `phone` TEXT NOT NULL,
//   `role` TEXT NOT NULL,
//   `active` TEXT NOT NULL,
//   `last` TEXT NOT NULL,
//   PRIMARY KEY  (`id`)
// );

// make connection
$db = new SQLite3('UserData1.db');
$tablename = 'users1';

$tableCheck =$db->query("SELECT name FROM sqlite_master WHERE name='$tablename'");
if ($tableCheck->fetchArray() === false){
  $first_run = true;
}


// create table if not apc_exists
$db-> exec("CREATE TABLE IF NOT EXISTS $tablename(
   id INTEGER PRIMARY KEY AUTOINCREMENT,
   erstellt TEXT NOT NULL DEFAULT (datetime('now','localtime')),
   geaendert TEXT NOT NULL DEFAULT (datetime('now','localtime')),
   name TEXT NOT NULL DEFAULT 'Name')");


if ($first_run){$db-> exec("INSERT INTO $tablename(geaendert, name)VALUES((datetime('now','localtime')), 'Paul')");}


// do the edits befor loading data
// Add a new column
if (isset($_POST['add_column'])) {
    $add = $_POST['add_column'];
    $db->exec("ALTER TABLE $tablename ADD COLUMN '$add' TEXT NOT NULL DEFAULT '' ");
    echo "<meta http-equiv=\"refresh\" content=\"0; url=index.php\" />";
}

// create a new data row
if (isset($_POST['create_data'])) {
    if (array_values($_POST) [1] == '') {
        echo 'Es muss ein Name angegeben werden';
    } else {
        // update the userrow
        $highest_ID = $db->querySingle("SELECT id FROM $tablename WHERE id = (SELECT MAX(id) FROM $tablename) order by id desc limit 1");
        $name = ['id'];
        $value = [$highest_ID + 1];
        foreach ($_POST as $param_name => $param_val) {
            if ($param_name != 'create_data' && $param_name != 'id') {
                $name[] = $param_name;
                $value[] = $param_val;
            }
        }
        $query = "INSERT INTO $tablename (" . implode(",", $name) . ") VALUES ('" . implode("','", $value) . "')";
        $db->exec($query);
    }
}

// update the row
if (isset($_POST['update_data'])) {
    $id = $_POST['id'];
    //echo "ID: ".array_values($row)[$id]['id'];
    print_r($_POST);
    foreach ($_POST as $param_name => $param_val) {
        if ($param_name != 'update_data' && $param_name != 'id' ) {
            $query = "UPDATE $tablename set  $param_name ='$param_val' WHERE id=$id ";
            $db->exec($query);
        }
    }
}

// delete row
if (isset($_GET['delete'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM $tablename WHERE id=$id";
		$db->exec($query);
}

// get the data
$result = $db->query("SELECT * FROM $tablename");
$result->fetchArray(SQLITE3_NUM);
$fieldnames = []; // all colum-names
$fieldtypes = [];
for ($colnum = 0;$colnum < $result->numColumns();$colnum++) {
    $fieldnames[] = $result->columnName($colnum);
    $fieldtypes[] = $result->columnType($colnum);
}
$result->reset();
while ($row = $result->fetchArray(SQLITE3_NUM)) {
    $rows[] = $row; // all data in $row
}


// // only show items from the list
// // neccesary for different user
//
// // item list, in future from the db
// $item_names = ['id','erstellt','geaendert','Name', 'Mail'];
// // get the key of the item in array
// $item_numbers = [];
// for ($i=0; $i<count($item_names); $i++){
// 	$item_numbers[] = array_search($item_names[$i], $fieldnames);
// }
//
// // make a new rows array with the item keys
// $new_rows = [];
// for ($i=0; $i<count($rows); $i++){
// 	for ($j=0; $j<count($item_numbers); $j++){
// 		$new_rows[$i][] = $rows[$i][$item_numbers[$j]];
// 	}
// }
// // replace the arrays with all data with the new list items
// unset($fieldnames);
// $fieldnames = $item_names;
// unset($rows);
// $rows = $new_rows;


?>

<!-- HTML & CSS for ALL -->
<!DOCTYPE html>
<html>
<head>
	<title>Data List</title>
	<style>
	table, tr, td{border-style:none;  border-bottom:1px solid #eee; border-collapse: collapse;}
	</style>
</head>
<body>
<br><br><br>
<!-- HTML & CSS for ALL -->

<?
// schow all data in index.php
if( !isset($_GET['update']) & !isset($_GET['create']) ){
?>
<div>
	<table width="100%" cellpadding="5" cellspacing="1" border="1">
		<tr>
			<?
			// Schleife macht aus den $fieldnames der DB Überschriften für die Tabelle
			foreach ($fieldnames AS $field){
				echo "<td>".$field."</td>\n";
			}
			echo "<td>  </td>\n";
			?></tr><?php
			// Schleift durch das multi Array, erst jeder User eine Reihe, dann jeder Item eine Splate
			for ($i = 0;$i < count($rows);$i++) {
			    echo "<tr>\n";
			    for ($j = 0;$j < count($rows[$i]);$j++) {
			        echo "<td>" . $rows[$i][$j] . "</td>\n";
			    }
			    echo "<td><a href=\"index.php?update&id=" . $rows[$i][0] . "\">bearbeiten</a></td>\n";
			    echo "</tr>\n";
			} ?>
		</table>
</div>
<div>
		<form action="index.php?update" method="post"><input type="text" name="add_column" id="add_column" maxlength="40"><button type="submit">neue Spalte</button></form>
		<a href="index.php?create">neue Zeile</a>
</div>
</body>
</html>
<?php
}

// form for create and update rows
if (isset($_GET['update']) or isset($_GET['create'])) {
		$id = $_GET['id'];
		// set the right name for POST form
		if( isset($_GET['update']) ){ $POST_name = 'update_data'; }
		if( isset($_GET['create']) ){ $POST_name = 'create_data'; }
    // // finde die Nummer des arrays, welches die $id aus der $GET_ hat
    $key = array_search($_GET['id'], array_column($rows, 0)); // echo "GET['id']: ".$_GET['id']."<br>";	// echo "key: ".$key."<br>";	// echo "rows_key".$rows[$key][0]."<br>";	// print_r($rows)."<br>";
		// local time
		$dt = new DateTime("now", new DateTimeZone('Europe/Berlin'));
		$local_time =  $dt->format('Y-m-d H:i:s');
		?>

		<div>
			<table width="100%" cellpadding="5" cellspacing="1" border="1">
				<form action="index.php" method="post">
					<input type="hidden" name="id" value="<?php echo $id; ?>">
					<input type="hidden" name="geaendert" value="<?php echo $local_time; ?>">
						<?
						// Schleife macht aus den $fieldnames der DB Überschriften für die Tabelle
						$i = 0;
						foreach ($fieldnames AS $field){
							if ($field != 'id' && $field != 'erstellt' && $field != 'geaendert'){
							echo "<tr>";
							echo "<td>$field:</td>";
							echo "<td><input name=\"$field\" type=\"text\" value=\"".trim($rows[$key][$i])."\"></td>";
							echo "</tr>"; }
							$i++;
						}
						?>
						<tr>
								<td><a href="index.php">Zur&uuml;ck</a></td>
								<td><input name="<? echo $POST_name; ?>" type="submit" value="Speichern"></td>
								<td><a href="index.php?delete&id=<? echo $id; ?>" onclick="return confirm('Soll der Mensch <?php echo strtoupper($rows[$i][3]); ?> wirklich gel&ouml;scht werden?');">delete</a></td>
						</tr>
					</form>
				</table>
			</div>
		</body>
</html>
<?
}
?>
