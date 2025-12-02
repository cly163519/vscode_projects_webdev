<?php
$db = null;
if (isset($_REQUEST['database']) && str_ends_with($_REQUEST['database'], ".sqlite")) {
    $db = new PDO("sqlite:{$_REQUEST['database']}");
}
function columns_info($tablename) {
    global $db;
    $ret = array();
    $result = $db->query("PRAGMA table_info($tablename);");
    while ($row = $result->fetch()) {
        $ret[$row['name']] = $row;
    }
    return $ret;
}
function show_tables() {
    global $db;
    echo "<a href=\"{$_SERVER['PHP_SELF']}\">Back to database selection</a>";
    $result = $db->query("SELECT * FROM sqlite_schema WHERE type = 'table'");
    echo "<table><tr><th>Table</th><th>Columns</th><th>Rows</th></tr>";
    while ($res = $result->fetchObject()) {
        echo "<tr><td><a href=\"?action=table&amp;table=$res->name&amp;database={$_REQUEST['database']}\">$res->name</a></td><td><ol>";
        $r2 = $db->query("PRAGMA table_info($res->name);");
        while ($res2 = $r2->fetch()) {
            $key = $res2['pk'] ? " PRIMARY KEY" : "";
            echo "<li>{$res2['name']} ({$res2['type']}$key)</li>";
        }
        echo "</ol></td><td>";
        $r2 = $db->query("SELECT COUNT(*) FROM $res->name");
        print $r2->fetchColumn();
        echo "</td></tr>";
    }
    echo "</table>";
?>
<form method="post">
    <input type="hidden" name="database" value="<?=$_REQUEST['database']?>">
    <input type="hidden" name="action" value="new_table">
    New table <input type="text" name="table"> with <input type="number" name="num_cols"> columns
    <input type="submit" value="Submit">
</form>
<form method="post">
    <input type="hidden" name="database" value="<?=$_REQUEST['database']?>">
    <input type="hidden" name="action" value="run_query">
    <div style="display: flex;">
        <input type="text" name="query" style="flex: 1">
        <input type="submit" value="Run Query">
    </div>
</form>
<?php
}

function show_table($tablename, $queryfields="*", $where=null) {
    global $db;
?>
<h2>Table `<?=$tablename?>`</h2>
<a href="?database=<?=$_REQUEST['database']?>">Back to table list</a>
<form method="post" action="managedb.php">
    <input type="hidden" name="database" value="<?=$_REQUEST['database']?>">
    <input type="hidden" name="table" value="<?=$tablename?>">
<?php
    $doneHeader = false;
    $fields = array();
    $types = array();
    $r2 = $db->query("PRAGMA table_info($tablename);");
    while ($res2 = $r2->fetch()) {
        $types[$res2['name']] = $res2['type'];
        if ($res2['pk'])
            $types[$res2['name']] = $res2['type'] . " PRIMARY KEY";
    }
    if (($queryfields == "*" || $queryfields == "") && !$where) {
        $r2 = $db->query("PRAGMA table_info($tablename);");
        echo "<table><tr><td></td>";
        while ($res2 = $r2->fetch()) {
            if ($res2['pk'])
                echo "<th>{$res2['name']} ({$res2['type']}🔑)</th>";
            else
                echo "<th>{$res2['name']} ({$res2['type']})</th>";
            $fields[] = $res2['name'];
        }
        echo "</tr>";

        $r2 = $db->query("SELECT rowid AS rowid, * FROM $tablename");
        echo "Query: <tt>SELECT * FROM $tablename;</tt><br>";
        $doneHeader = true;
    } elseif ($where) {
        if (!$queryfields)
            $queryfields = "*";
        $r2 = $db->query("SELECT rowid as rowid, $queryfields FROM $tablename WHERE $where");
        echo "Query: <tt>SELECT $queryfields FROM $tablename WHERE $where;</tt><br>";
        echo "<table>";
    } else {
        $r2 = $db->query("SELECT rowid as rowid, $queryfields FROM $tablename");
        echo "Query: <tt>SELECT $queryfields FROM $tablename;</tt><br>";
        echo "<table>";
    }
    $rownum = 0;
    while ($res2 = $r2->fetch(PDO::FETCH_ASSOC)) {
        if (!$doneHeader) {
            $doneHeader = true;
            echo "<tr><td></td>";
            foreach ($res2 as $k=>$v) {
                if ($k == 'rowid') continue;
                $typeinfo = "";
                if (isset($types[$k]))
                    $typeinfo = " ($types[$k])";
                echo "<th>$k$typeinfo</th>";
                $fields[] = $k;
            }
            echo "</tr>";
        }
        echo "<tr>";
        echo "<td>";
        $rowid = $res2['rowid'];
?>
<form method="post" action="">
    <input type="hidden" name="database" value="<?=$_REQUEST['database']?>">
    <input type="hidden" name="table" value="<?=$tablename?>">
    <input type="hidden" name="rowid" value="<?=$rowid?>">
    <button name="action" value="delete">Delete</button>
    <button name="action" value="edit">Edit</button>
</form>
<?php
        echo "</td>";
        foreach ($fields as $k) {
            $align = (isset($types[$k]) && str_starts_with($types[$k], "INT")) ? ' align=right' : '';
            echo "<td$align>" . htmlspecialchars($res2[$k]) . "</td>";
        }
        echo "</tr>";
        $rownum++;
    }
    echo "<tr><td></td>";
    foreach ($fields as $n) {
        echo "<td><input type=\"text\" name=\"field_$n\" size=10></td>";
    }
    echo "</tr>";
    echo "</table>";
?>
<button name="action" value="insert">
    Insert New Row
</button>
</form>
<form method="post" action="managedb.php">
    <input type="hidden" name="database" value="<?=$_REQUEST['database']?>">
    <input type="hidden" name="table" value="<?=$tablename?>">
    <!-- <input type="hidden" name="action" value="select"> -->
    <select name="action">
        <option value="select">SELECT</option>
        <option value="update">UPDATE</option>
    </select> <span class="select"><input type="text" name="fields" placeholder="*"> FROM </span><?=$tablename?> <span class="update">SET <input type="text" name="sets"></span> WHERE <input type="text" name="where">
    <input type="submit" value="Run Query">
</form>
<style>
select:not(:has(option[value="select"]:checked)) ~ .select {
    display: none;
}
select:not(:has(option[value="update"]:checked)) ~ .update {
    display: none;
}
</style>
<?php
}
function insert_row($tablename, $fields) {
    global $db;
    $columns_info = columns_info($tablename);
    foreach ($fields as $k=>$v) {
        if (!$v && $columns_info[$k]['pk']) {
            unset($fields[$k]);
            break;
        }
    }
    $query = "INSERT INTO $tablename (" . join(", ", array_keys($fields)) . ") VALUES (";
    $qs = array();
    foreach ($fields as $k=>$v) {
        $qs[] = "?";
    }
    $query .= join(", ", $qs) . ")";
    echo 'Query: <tt>' . htmlspecialchars($query) . '</tt><br>';
    $stmt = $db->prepare($query);
    $i = 1;
    foreach(array_values($fields) as $v) {
        $stmt->bindValue($i, $v);
        echo "Binding $i to " . htmlspecialchars($v) . "<br>";
        $i++;
    }
    $stmt->execute();
}
function delete_row($tablename, $rowid) {
    global $db;
    // $db->query("DELETE FROM $tablename WHERE rowid = (SELECT rowid FROM $tablename LIMIT $rownum, 1)");
    $db->query("DELETE FROM $tablename WHERE rowid = $rowid");
    show_table($tablename);
}
function edit_row($tablename, $rowid) {
    global $db;
    echo "<h2>Editing row in $tablename</h2><a href=\"?database={$_REQUEST['database']}&amp;table=$tablename\">Back to $tablename</a>";
    $result = $db->query("SELECT * FROM $tablename WHERE rowid = $rowid");
    $row = $result->fetch(PDO::FETCH_ASSOC);
?>
<form method="post">
    <input type="hidden" name="database" value="<?=$_REQUEST['database']?>">
    <input type="hidden" name="table" value="<?=$tablename?>">
    <input type="hidden" name="action" value="update_row">
    <input type="hidden" name="rowid" value="<?=$rowid?>">
<table>
<?php
foreach ($row as $k=>$v) {
?>
<tr><td><?=$k?></td><td><input type="text" value="<?=htmlspecialchars($v)?>" name="field_<?=$k?>"></tr>
<?php
}
?>
</table>
<input type="submit" value="Update row">
</form>
<?php
}
function update_row($tablename, $rowid, $fields) {
    global $db;
    $query = "UPDATE $tablename SET ";
    $qs = array();
    foreach ($fields as $k=>$v) {
        $qs[] = "$k = ?";
    }
    $query .= join(", ", $qs);
    $query .= " WHERE rowid = $rowid";
    echo $query . "<br>";
    $stmt = $db->prepare($query);
    $fieldnum = 1;
    foreach ($fields as $k=>$v) {
        $stmt->bindValue($fieldnum, $v);
        echo "Binding field $fieldnum to value " . htmlspecialchars($v) . "<br>";
        $fieldnum++;
    }
    $stmt->execute();
    echo "Updated row.";
    show_table($tablename);
}
function update_query($tablename, $sets, $where) {
    global $db;
    $query = "UPDATE $tablename SET $sets WHERE $where";
    echo "Query: <tt>" . htmlspecialchars($query) . "</tt><br>";
    $res = $db->query($query);
    if ($res)
        echo "Updated " . $res->rowCount() . " rows.<br>";
    else {
        echo "Error running update query: ";
        var_dump($db->errorInfo());
    }
    show_table($tablename);
}
function new_table($tablename, $numcolumns) {
?>
<form method="post">
    <input type="hidden" name="database" value="<?=$_REQUEST['database']?>">
    <input type="hidden" name="action" value="create_table">
    <input type="hidden" name="table" value="<?=$_POST['table']?>">
    <input type="hidden" name="num_cols" value=<?=$numcolumns?>>
    <table>
    <tr><th>Column Name</th><th>Column Type</th>
    <th>Primary Key<input type="radio" name="primary" value="" checked></th>
    </tr>
    <?php
    for ($i = 0; $i < $numcolumns; $i++) {
    ?>
    <tr>
        <td><input type="text" name="column_<?=$i?>"></td>
        <td>
            <select name="type_<?=$i?>">
                <option value="TEXT">TEXT</option>
                <option value="INTEGER">INTEGER</option>
                <option value="REAL">REAL</option>
                <option value="BLOB">BLOB</option>
            </select>
        </td>
        <td>
            <input type="radio" name="primary" value="<?=$i?>">
        </td>
    </tr>
    <?php
    }
    ?>
    </table>
    <input type="submit" value="Create Table">
</form>
<?php
}
function create_table($tablename, $num_cols) {
    global $db;
    $query = "CREATE TABLE $tablename (";
    for ($i = 0; $i < $num_cols; $i++) {
        $query .= $_POST["column_$i"] . " " . $_POST["type_$i"];
        if ($_POST['primary'] == $i)
            $query .= " PRIMARY KEY";
        if ($i + 1 < $num_cols)
            $query .= ", ";
    }
    $query .= ")";
    echo "Query: <tt>" . htmlspecialchars($query) . "</tt><br>";
    $db->query($query);
    show_table($tablename);
}
function run_query($query) {
    global $db;
    echo '<a href="' . $_SERVER['PHP_SELF'] . '?database=' . $_REQUEST['database'] . '">Back to table list</a>';
    echo '<br>Query: <tt>' . htmlspecialchars($query) . '</tt>';
    $result = $db->query($query);
    $first = true;
    echo '<table>';
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        if ($first) {
            $first = false;
            echo '<tr>';
            foreach ($row as $k=>$v) {
                echo '<th>' . htmlspecialchars($k) . '</th>';
            }
            echo '</tr>';
        }
        echo '<tr>';
        foreach ($row as $k=>$v) {
            echo '<td>';
            echo htmlspecialchars($v);
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
?>
<form method="post">
    <input type="hidden" name="database" value="<?=$_REQUEST['database']?>">
    <input type="hidden" name="action" value="run_query">
    <div style="display: flex;">
        <input type="text" name="query" value="<?=htmlspecialchars($query)?>" style="flex: 1">
        <input type="submit" value="Run Query">
    </div>
</form>
<?php
}
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Manage SQLite Database</title>
    </head>
    <body>
<?php

if ($db) {
    echo "<h1>Database {$_REQUEST['database']}</h1>";
}
if (!$db) {
?>
<form method="get">
    <label for="database">Database filename:</label>
    <input type="text" name="database" id="database">
    <input type="submit" value="Show Tables">
</form>
<?php
    if ($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
        echo "<strong>Warning! This script is not safe to expose to other users. Currently accessing from remote address {$_SERVER['REMOTE_ADDR']}, not local loopback.";
    }
} else if (!isset($_REQUEST['action'])) {
    show_tables();
} else if ($_REQUEST['action'] == 'table') {
    show_table($_REQUEST['table']);
} else if ($_REQUEST['action'] == 'insert') {
    $fields = array();
    foreach ($_REQUEST as $k=>$v) {
        if (str_starts_with($k, "field_")) {
            $fields[substr($k, 6)] = $v;
        }
    }
    insert_row($_REQUEST['table'], $fields);
    show_table($_REQUEST['table']);
} else if ($_REQUEST['action'] == 'delete') {
    delete_row($_REQUEST['table'], $_REQUEST['rowid']);
} else if ($_REQUEST['action'] == 'edit') {
    edit_row($_REQUEST['table'], $_REQUEST['rowid']);
} else if ($_REQUEST['action'] == 'update_row') {
    $fields = array();
    foreach ($_REQUEST as $k=>$v) {
        if (str_starts_with($k, "field_")) {
            $fields[substr($k, 6)] = $v;
        }
    }
    update_row($_REQUEST['table'], $_REQUEST['rowid'], $fields);
} else if ($_REQUEST['action'] == 'select') {
    show_table($_REQUEST['table'], $_REQUEST['fields'], $_REQUEST['where']);
} else if ($_REQUEST['action'] == 'update') {
    update_query($_REQUEST['table'], $_REQUEST['sets'], $_REQUEST['where']);
} else if ($_REQUEST['action'] == 'new_table') {
    new_table($_REQUEST['table'], $_REQUEST['num_cols']);
} else if ($_REQUEST['action'] == 'create_table') {
    create_table($_REQUEST['table'], $_REQUEST['num_cols']);
} else if ($_REQUEST['action'] == 'run_query') {
    run_query($_POST['query']);
} else {
    show_tables();
}
?>
</table>

</body>
</html>