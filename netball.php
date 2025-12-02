
<?php

$conn = new PDO("sqlite:netball.sqlite");
$mode = 'list';
if (isset($_REQUEST['mode']))
	$mode = $_REQUEST['mode'];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Netball database</title>
	</head>
	<body>
		<ul>
			<li><a href="?mode=list">List teams</a>
			<li><a href="?mode=coaches">List teams with coaches</a>
			<li><a href="?mode=coaches_captains">List teams with coaches and captains</a>
			<li><a href="?mode=list_players">List players on a particular team</a>
			<li><a href="?mode=show_player">Show individual player bio details, including team and positions</a>			
			<li><a href="?mode=list_by_team_position">List players on a particular team who play a particular position</a>
			<li><a href="?mode=edit_player">Edit a player</a>
			<li><a href="?mode=remove_player">Remove player</a>
			<li><a href="?mode=add_player">Add new player</a>
			<li><a href="?mode=results_table">Generate a table of results across the league</a>
		</ul>
<?php
	if ($mode == 'list') {
?>
		<h1>List of teams</h1>
		<table>
<?php
		$result = $conn->query("SELECT * FROM teams;");
		foreach ($result as $row) {
?>
			<tr><td><?php echo $row['name']?></td></tr>
<?php
		}
?>
		</table>
<?php

} elseif ($mode == 'coaches') {
?>
    <h1>List teams with coaches</h1>
    <table>
        <tr><th>Team</th><th>Coach</th></tr>
<?php
    
    $query = $conn->query("
        SELECT teams.name AS team_name, coaches.name AS coach_name
        FROM teams
        JOIN coaches ON teams.coach = coaches.id;
    ");

    foreach ($query as $row) {
?>
        <tr>
            <td><?= htmlspecialchars($row['team_name']) ?></td>
            <td><?= htmlspecialchars($row['coach_name']) ?></td>

        </tr>
<?php
    }
?>
    </table>
<?php

} elseif ($mode == 'coaches_captains') {
?>
    <h1>List teams with coaches and captains</h1>
    <table>
        <tr><th>Team</th><th>Coach</th><th>Captain</th></tr>
<?php
    
    $query = $conn->query("
        SELECT teams.name AS team_name, coaches.name AS coach_name, players.name AS captain_name
        FROM teams
        JOIN coaches ON teams.coach = coaches.id
        JOIN players ON teams.captain = players.id;

    ");

    foreach ($query as $row) {
?>
        <tr>
            <td><?= htmlspecialchars($row['team_name']) ?></td>
            <td><?= htmlspecialchars($row['coach_name']) ?></td>
            <td><?= htmlspecialchars($row['captain_name']) ?></td>
        </tr>
<?php
    }
?>
    </table>
<?php

} elseif ($mode == 'list_players') {
	$team = 0;
	if (isset($_REQUEST['team']))
		$team = $_REQUEST['team'];
?>
		<h1>List of players on team</h1>
		<form method="get">
		<select name="team">
<?php
		$result = $conn->query("SELECT * FROM teams;");
        # or while ($row = $result->fetch()) {
		foreach ($result as $row) {
?>
			<option value="<?=$row['id']?>"<?php
				# This selects the current team we're looking at, if any
				if ($team == $row['id']) {echo " selected";}
			?>><?=$row['name']?></option>
<?php
		}
?>
		</select>
		<input type="hidden" value="list_players" name="mode" />
		<input type="submit" value="Submit" />
		</form>
<?php
		if ($team) {
			$query = $conn->prepare("SELECT * FROM players WHERE team = ? ;");
			$query->bindValue(1, $team, PDO::PARAM_INT);
			$query->execute();
?>
		<table>
<?php
            # or while ($row = $query->fetch()) {
			foreach ($query as $row) {
?>
			<tr><td><?php echo $row['name']?></td></tr>
<?php
			}
		}
?>
		</table>
<?php

} elseif ($mode == 'show_player') {

    $player = 0;
    if (isset($_REQUEST['player']))
        $player = $_REQUEST['player'];
?>
    <h1>List individual player bio details</h1>

    <form method="get">
    <select name="player">
<?php
       
        $result = $conn->query("SELECT * FROM players ORDER BY name;");
        foreach ($result as $row) {
?>
            <option value="<?=$row['id']?>"<?php
                if ($player == $row['id']) echo " selected";
            ?>><?=$row['name']?></option>
<?php
        }
?>
        </select>

        <input type="hidden" name="mode" value="show_player">
        <input type="submit" value="Submit">
    </form>
<?php

    if ($player) {

        $query = $conn->prepare("
            SELECT players.name AS player_name,
                   teams.name AS team_name,
                   player_positions.position AS position_name
            FROM players
            JOIN teams ON players.team = teams.id
            JOIN player_positions ON players.id = player_positions.player_id
            WHERE players.id = ?;
        ");

        $query->execute([$player]);

        $player_name = "";
        $team_name = "";
        $positions = [];

        foreach ($query as $row) {
            $player_name = $row['player_name'];
            $team_name = $row['team_name'];
            $positions[] = $row['position_name'];
        }
?>
        <h2>Player Bio</h2>
        <table cellpadding="5" style="border-collapse: collapse;">
            <tr>
                <td style="text-align:left;">Player</td>
                <td style="text-align:left;">Team</td>
                <td style="text-align:left;">Positions</td>
           </tr>
        <tr>
            <td><?= htmlspecialchars($player_name) ?></td>
            <td><?= htmlspecialchars($team_name) ?></td>
            <td><?= htmlspecialchars(implode(", ", $positions)) ?></td>
        </tr>
        </table>
<?php
    }


} elseif ($mode == 'list_by_team_position') {

    $team = 0;
    $position = 0;

    if (isset($_REQUEST['team']))
        $team = $_REQUEST['team'];

    if (isset($_REQUEST['position']))
        $position = $_REQUEST['position'];
?>
    <h1>List position</h1>

    <form method="get">

        <!-- Select Team -->
        <select name="team">
<?php
        $teams = $conn->query("SELECT * FROM teams ORDER BY name;");
        foreach ($teams as $row) {
?>
            <option value="<?= $row['id'] ?>"<?php
                if ($team == $row['id']) echo " selected";
            ?>><?= $row['name'] ?></option>
<?php
        }
?>
        </select>

        <!-- Select Position -->
        <select name="position">
<?php
        $positions_list = $conn->query("SELECT DISTINCT position FROM player_positions ORDER BY position;");
        foreach ($positions_list as $row) {
?>
            <option value="<?= $row['position'] ?>"<?php
                if ($position == $row['position']) echo " selected";
            ?>><?= $row['position'] ?></option>
<?php
        }
?>
        </select>

        <input type="hidden" name="mode" value="list_by_team_position">
        <input type="submit" value="Submit">
    </form>
<?php

    if ($team && $position) {

        $query = $conn->prepare("
            SELECT players.name AS player_name,
                   teams.name AS team_name,
                   player_positions.position AS position_name
            FROM players
            JOIN teams ON players.team = teams.id
            JOIN player_positions ON players.id = player_positions.player_id
            WHERE players.team = ?
              AND player_positions.position = ?
            ORDER BY players.name;
        ");

        $query->execute([$team, $position]);

        $results = $query->fetchAll();
?>
        <h2>Matching Players</h2>
        <table cellpadding="5" style="border-collapse: collapse;">
            <tr>
                <td style="text-align:left;">Player</td>
                <!-- <td style="text-align:left;">Team</td> -->
                <!-- <td style="text-align:left;">Position</td> -->
            </tr>

<?php
        foreach ($results as $row) {
?>
            <tr>
                <td><?= print_r($row) ?></td>
                <td><?= htmlspecialchars($row['player_name']) ?></td>
                <!-- <td><?= htmlspecialchars($row['team_name']) ?></td> -->
                <!-- <td><?= htmlspecialchars($row['position_name']) ?></td> -->
            </tr>
<?php
        }

        if (count($results) == 0) {
?>
            <tr>
                <td colspan="3" style="color:red;">No players match your criteria.</td>
            </tr>
<?php
        }
?>
        </table>
<?php
    }

} elseif ($mode == 'edit_player') {
$player = 0;
    if (isset($_REQUEST['player']))
        $player = $_REQUEST['player'];
?>
<h1>Edit a Player</h1>

<form method="get">
    <select name="player">
<?php
    $result = $conn->query("SELECT id, name FROM players ORDER BY name;");
    foreach ($result as $row) {
?>
        <option value="<?=$row['id']?>"<?php if ($player == $row['id']) echo " selected"; ?>>
            <?=$row['name']?>
        </option>
<?php
    }
?>
    </select>

    <input type="hidden" name="mode" value="edit_player">
    <input type="submit" value="Choose player">
</form>
<?php

 if ($player) {

        // 取出该球员旧信息
        $query = $conn->prepare("SELECT * FROM players WHERE id = ?");
        $query->execute([$player]);
        $info = $query->fetch();
?>
<form method="post">
    <input type="hidden" name="mode" value="edit_player">
    <input type="hidden" name="id" value="<?=$info['id']?>">

    <p>Name: <input type="text" name="name" value="<?=$info['name']?>"></p>
    <p>Height: <input type="text" name="height" value="<?=$info['height']?>"></p>
    <p>Hometown: <input type="text" name="hometown" value="<?=$info['hometown']?>"></p>
    <p>Team:
        <select name="team">
<?php
    $teams = $conn->query("SELECT id, name FROM teams;");
    foreach ($teams as $t) {
?>
            <option value="<?=$t['id']?>"<?php if ($info['team'] == $t['id']) echo " selected"; ?>>
                <?=$t['name']?>
            </option>
<?php
    }
?>
        </select>
    </p>

    <input type="submit" name="update" value="Update player">
</form>
<?php
    }

if (isset($_POST['update'])) {

        $query = $conn->prepare("
            UPDATE players
            SET name = ?,
                height = ?,
                hometown = ?,
                team = ?
            WHERE id = ?;
        ");

        $query->execute([
            $_POST['name'],
            $_POST['height'],
            $_POST['hometown'],
            $_POST['team'],
            $_POST['id']
        ]);

        echo "<p><b>Player updated successfully!</b></p>";
    }

} elseif ($mode == 'remove_player') {







} elseif ($mode == 'add_player') {







} elseif ($mode == 'results_table') {



}else {
	print "Mode '$mode' is not implemented yet.\n";
	print "Add another branch of the 'if' to complete it.";
}
?>
	</body>
</html>

