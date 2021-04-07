<?php
require_once '../static/functions/connect.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$needScore = isset($_GET['score']) ? true : false;
if ($stmt = $conn -> prepare("SELECT `submission`.`result` as result,`submission`.`score` as score,`submission`.`maxScore` as maxScore,`problem`.`score` as probScore FROM `submission` INNER JOIN `problem` ON `problem`.`id` = `submission`.`problem` WHERE `submission`.`id` = ? LIMIT 1")) {
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $subResult = $row['result'] != 'W' ? $row['result']: 'รอผลตรวจ...';
            $subScore = $row['maxScore'] != 0 ? ($row['score']/$row['maxScore'])*$row['probScore'] : "UNDEFINED";  ?>
<code><?php echo $subResult;?><?php if ($needScore && $row['result'] != 'W') echo ' (' . $subScore . ')'; ?></code>
        <?php }
        $stmt->free_result();
        $stmt->close();  
    }
}
?>