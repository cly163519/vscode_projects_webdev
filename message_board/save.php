<?php
// 读取原始 JSON 数据
$data = json_decode(file_get_contents("php://input"), true);

$msg = $data["message"];

// 读取已有的留言文件
$list = json_decode(file_get_contents("data.json"), true);

// 加入新的留言
$list[] = $msg;

// 把结果写回文件
file_put_contents("data.json", json_encode($list));

// 输出 JSON 给前端
echo json_encode(["msg" => "留言已保存！"]);
?>
