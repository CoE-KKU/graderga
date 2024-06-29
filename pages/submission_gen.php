<?php
    require_once '../static/functions/connect.php';
    require_once '../static/functions/function.php';
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 1;
    $wholookthis = $_POST['who'];
    if ($stmt = $conn -> prepare("SELECT * FROM `submission` WHERE id = ? LIMIT 1")) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            while ($row = $result->fetch_assoc()) {
                $subID = $row['id'];
                $subUser = (new User($row['user']))->getDisplayname();
                $subProb = $row['problem'];
                $subLang = $row['lang'];
                $subResult = $row['result'] != 'W' ? $row['result']: 'รอผลตรวจ...';
                $subRuntime = $row['runningtime']; //ms
                $subMemory = $row['memory'] ? $row['memory'] . " MB": randomErrorMessage(); //MB
                $subUploadtime = $row['uploadtime']; ?>
                
                Timestamp: <?php echo $subUploadtime; ?>
                <br>User: <code><?php echo $subUser; ?></code>
                <br>Problem: <a href="../problem/<?php echo $subProb; ?>"><?php $spb = new Problem($subProb); echo $spb->display(); ?></a>
                <br>Language: <code><?php echo $subLang; ?></code>
                <br>Result: <code <?php if ($row['result'] == 'W') echo "data-sub-id='$id' data-wait=true"; ?>><?php echo $subResult; ?></code>
                <br>Running Time: <code><?php echo $subRuntime; ?> ms</code>
                <!-- <br>Memory: <code><?php //echo $subMemory; ?></code> -->
                
                <?php if ($row['user'] == $wholookthis || isAdmin()) {
                    if ($row['comment'] != "End of Test" && trim($row['comment']) != "")
                        echo "<br>Judge Response:<br><pre><code>" . trim($row['comment']) . "</code></pre>";
                    echo "<br>Submitted Code: ";
                    
                    if (file_exists($row['script'])) {
                        echo "<br><pre><code>";
                        $r = file_get_contents($row['script']);
                        $r = str_replace("<", "&lt;", $r); //Make browser don't think < is the start of html tag
                        $r = str_replace(">", "&gt;", $r); //Make browser don't think < is the end of html tag
                        echo ($r);
                        echo "</code></pre>";
                        ?>
                        <div class="text-right mb-0 mt-0"><small><a href="/<?php echo bucket_encrypting(str_replace("..","",$row['script'])); ?>" download="<?php echo $subID.".".pathinfo($row['script'], PATHINFO_EXTENSION);?>" target="_blank">Download Code <i class="fas fa-download"></i></a></small></div>
                        <?php
                    } else {
                        echo "<span class='text-danger'>MISSING FILE</span>";
                    } ?>
                    <?php 
                } ?>
            <?php }
            $stmt->free_result();
            $stmt->close();  
        }
    }
?>