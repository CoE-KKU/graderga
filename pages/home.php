<?php if (in_array((int) date("dm"), array(2212,1103,810))) { ?>
    <!-- <div id="emojiRain"></div><script src="../vendor/emojirain.js"></script> -->
<?php } ?>
<div class="homepage" style="padding-top: 64px; min-height: 100vh;">
    <div class="container-fluid h-100 w-100">
        <div class="h-100 w-100 row align-items-center">
            <div class="d-none d-lg-block col-lg-1"></div>
            <div class="col-12 col-lg-5">
                <div class="bounceInDown animated">
                    <h1 class="font-weight-bold text-coekku display-4">
                        <?php
                            $randomNumber = mt_rand(1, 100);
                            if ($randomNumber >= 90) {
                                echo "ガレダミ";
                            } else {
                                echo "Garedami";
                            }
                        ?>
                    </h1>
                    <h4 class="font-weight-normal">Computer Engineering's Student-Made grader.</h4>
                    <a class="btn btn-coekku" href="../problem/">เริ่มทำโจทย์กันเลย !</a>
                    <a class="btn btn-outline z-depth-0 btn-rounded btn-coekku mt-3 mb-3" href="../problem/">Getting Started</a>
                    <?php
                    if ($stmt = $conn -> prepare("SELECT `codename`,`id`,`name`,`properties` FROM `problem` WHERE JSON_EXTRACT(`properties`,'$.hide') = 0 AND UNIX_TIMESTAMP() - JSON_EXTRACT(`properties`,'$.last_hide_updated') <= 604800 AND JSON_EXTRACT(`properties`,'$.last_hide_updated') > 0 ORDER BY JSON_EXTRACT(`properties`,'$.last_hide_updated') DESC limit 7")) {
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if ($result->num_rows > 0) { ?>
                            <div class="bounceInLeft delay-1s animated">
                            <h5 class="rainbow mt-3">New Problem!!!</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm d-table" id="problemTable">
                                    <thead>
                                        <tr class="text-nowrap">
                                            <th scope="col" class="font-weight-bold text-coekku text-right">ID</th>
                                            <th scope="col" class="font-weight-bold text-coekku">Task</th>
                                            <th scope="col" class="font-weight-bold text-coekku">Rate</th>
                                        </tr>
                                    </thead>
                            <?php while ($row = $result->fetch_assoc()) {
                                $id = $row['id']; $name = $row['name']; $codename = $row['codename']; 
                                    
                                    $prop = json_decode($row['properties'],true);
                                    $rate = array_key_exists("rating", $prop) ? $prop["rating"] : 0;
                                
                                    echo "<tr onclick='window.open(\"../problem/$id\")'>
                                        <th class='text-right' scope='row'><a href=\"../problem/$id\" target=\"_blank\">$id</a></th>
                                        <td><a href=\"../problem/$id\" target=\"_blank\">$name <span class='badge badge-coekku'>$codename</span></a></td>
                                        <td data-order='".$rate."'>".rating($rate)."</td>
                                    </tr>";
                                } ?>
                            </table></div></div>
                            <?php }
                            $stmt->free_result();
                            $stmt->close();  
                        } ?>
                </div>
                <div class="fadeIn animated">
                    <img src="../static/elements/3323617.svg" onContextMenu="return false;" class="mt-3 img-fluid w-100 d-block d-lg-none">
                </div>
            </div>
            <div class="col-12 col-lg-6 d-none d-lg-block">
                <div class="fadeIn animated">
                    <img src="../static/elements/3323617.svg" onContextMenu="return false;" class="img-fluid w-100">
                </div>
            </div>
        </div>
    </div>
</div>