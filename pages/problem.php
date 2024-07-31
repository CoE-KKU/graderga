<?php $admin = isAdmin(); ?>
<div class="container mb-3" style="padding-top: 88px;" id="container">
    <h1 class="display-4 font-weight-bold text-center text-coekku">Problem</h1>
    <?php if ($admin) { ?><a href="../problem/create" class="btn btn-coekku btn-sm">+ Add Problem</a><?php } ?>
    <div class="table-responsive">
        <table class="table table-hover w-100 d-block d-md-table" id="problemTable">
            <thead>
                <tr class="text-nowrap">
                    <th scope="col" class="font-weight-bold text-coekku text-right">ID</th>
                    <th scope="col" class="font-weight-bold text-coekku">Task</th>
                    <th scope="col" class="font-weight-bold text-coekku">Rate</th>
                    <th scope="col" class="font-weight-bold text-coekku">Result</th>
                </tr>
            </thead>
            <tbody class="text-nowrap">
                <?php
                $userID = isLogin() ? $_SESSION['user']->getID() : 0;
                if ($stmt = $conn -> prepare("WITH LatestSubmissions AS (SELECT s.problem AS probID, s.result AS subResult, ROW_NUMBER() OVER (PARTITION BY s.problem ORDER BY s.id DESC) AS rn FROM submission s WHERE s.user = ?) SELECT p.id AS probID, p.name AS probName, p.properties AS probProp, p.codename AS probCode, l.subResult FROM problem p LEFT JOIN LatestSubmissions l ON p.id = l.probID AND l.rn = ?;")) {
                    $stmt->bind_param('ii', $userID,$userID);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $id = $row['probID']; $name = htmlspecialchars($row['probName']); $codename = htmlspecialchars($row['probCode']);
                            
                            $prop = json_decode($row['probProp'],true);
                            $hide = array_key_exists("hide", $prop) ? $prop["hide"] : false;
                            $rate = array_key_exists("rating", $prop) ? $prop["rating"] : 0;

                            $hideMessage = ($hide) ? "<span class='badge badge-danger'>ซ่อน</span>" : "";
                            if (!$hide || $admin) {
                                $lastResult = $row['subResult'];
                                $color;
                                if (empty($lastResult)) $color = "";
                                else if (str_contains($lastResult, "-") || str_contains($lastResult, "X") || str_contains($lastResult, "T") || str_contains($lastResult, "Compile Error"))
                                    $color = "yellow lighten-4";
                                else $color = "green accent-1";
                                
                                echo "<tr style='cursor: pointer;' class='$color' onmousedown='window.open(\"../problem/$id\")'>
                                    <th class='text-right' scope='row'>$id</th>
                                    <td>$name <span class='badge badge-coekku'>$codename</span> $hideMessage</td>
                                    <td data-order='".$rate."'><b>".rating($rate)."</b></td>
                                    <td><code>$lastResult</code></td>
                                </tr>";
                            }
                        }
                        $stmt->free_result();
                        $stmt->close();  
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        $(document).ready(function () {
            $('#problemTable').DataTable({
                "lengthMenu": [ [15, 50, 100, -1], [15, 50, 100, "All"] ],
                'columnDefs': [ {
                    'targets': [1,3], // column index (start from 0)
                    'orderable': false, // set orderable false for selected columns
                }],
                "order": [[ 0, "desc" ]]
            });
            $('.dataTables_length').addClass('bs-select');
        });
    </script>
</div>