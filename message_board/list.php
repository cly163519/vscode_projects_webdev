<?php
header("Content-Type: application/json");

// 读取 data.json
echo file_get_contents("data.json");
?>
