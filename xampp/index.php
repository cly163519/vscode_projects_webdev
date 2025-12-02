<!DOCTYPE html>
<html lang="en">
	
<head>
	<title>This is my first web</title>
	<link rel="stylesheet" href="index.css" />

</head>
	<body>
		<h1>Hello world!</h1>
		<div id="sidebar">
			
			Guess:
			<input type="text" id = "guess">
			<button id="make_guess">Guess</button>
			hello
			<br>
			hello
			<br>
			hello
			<br>
			
		</div>
		<main>
			<?php
			print "Hello, <strong>world</strong>!";
			for ($i = 0; $i < 10; $i++) {
				print "Hello again for the {$i}th time. ";
			}
			$arr = array();
			$arr[0] = "hello";
			array_push($arr, "world");
			$arr[] = "goodbye";
			$arr["hello"] = "world";
			foreach ($arr as $k=>$v) {
				print "$k = $v<br>";
			}
			// print $arr[2];
			// print "after the warning";
			?>
			<p>
				<?php
				function myfunc($a, $b) {
					return $a + $b;
				}
				if (isset($_REQUEST['who'])) {
					$obj['key'] = $_REQUEST['who'];
					file_put_contents("data.json", json_encode($obj));
					// This is bad code!
					?>Hello, <?=$_REQUEST['who']?>!<?php
				}
				?>
			</p>
			<form method="post" action="index.php">
				<input type="text" name="who">
				<input type="submit" value="Send to server">
			</form>
		
		<p>My cat is very <strong>grumpy</strong></p>
		<p>This <a href = "second.html">is the <em>second</em> page.</a></p>
		<img
		  src="https://raw.githubusercontent.com/mdn/beginner-html-site/gh-pages/images/firefox-icon.png"
		  alt="Firefox icon" />
		  
		  <table>
			<caption>
				Front-end developer information
			</caption>
			<thead>
				<tr>
					<th scope="col">Name</th>
					<th scope="col">Most interest in</th>
					<th scope="col">Age</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<th scope="row">Tom</th>
					<td>HTML table</td>
					<td>22</td>
				</tr>
				<tr>
					<th scope="row">Jack</th>
					<td>Web accessibility</td>
					<td>45</td>
				</tr>
				<tr>
					<th scope="row">Rose</th>
					<td>Javascript frameworks</td>
					<td>29</td>
				</tr>
				<tr>
					<th scope="row">Karen</th>
					<td>Web performance</td>
					<td>36</td>
				</tr>
			</tbody>
			</table>
		  <ol>
			<li>list1</li>
			<li>list2</li>
		  </ol>
		  <details>
			<summery>ABCDEFG
				HIJKLMN
			</summery>
			</details>
		  
		</main>  
		<my-element before="Counting words: ">
			<style>
				span.count{
					color: green;
				}
				div{
					border: 2px solide black;;
				}
			</style>
			<div>
				"Counting words: 
				<slot></slot>
				<span class="count"></span>
			</div>
			<i>custom</i>
				element
		</my-element>
		<script src="guessing.js"></script>
		<script src="modules.js"></script>
		<script type="module" src="myelement.js"></script>
		</main>	
	</body>
</html>