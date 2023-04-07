<div class="container mb-3" style="padding-top: 88px; min-height: 100vh !important;" id="container">
    <h1 class="display-4 font-weight-bold text-center text-coekku">Donation</h1>
    <div class="row">
        <div class="col-12 col-md-8">
            <div class="d-none d-md-block" style="padding-top: 8vh;"></div>
            <div class="d-flex justify-content-center"><img src="https://i.ibb.co/tb6g1hf/kbankqr.jpg" class="mb-3" width="300" /></div>
            <h5 class="text-center font-weight-bold">Palapon Soontornpas / พลภณ สุนทรภาส</h5>
            <p class="text-center"><a href="mailto:palapon@kkumail.com">palapon@kkumail.com</a></p>
            <p class="text-center">
                <b>KBank</b> : <code style="color: green;">084-3-24454-8</code> <small class="text-muted">*ไม่ใช่เบอร์โทรศัพท์</small><br>
                <b>SCB</b> : <code style="color: purple;">551-442288-3</code><br>
                <b>Promptpay / TrueWallet</b> : <code style="color: blue;">090-8508007</code><br>
                <center><a id="donateSubmit" class="btn btn-coekku btn-md">ส่งเอกสารการโอนเงินโดเนท</a></center>
                <script>
                    $("#donateSubmit").on('click', function() {
                        window.open('./submit','popUpWindow','height=200,width=500,left=100,top=100,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
                    });
                </script>
            </p>
        </div>
        <div class="col-12 col-md-4">
        <div class="d-none d-md-block" style="padding-top: 5vh;"></div>
            <div class="table-responsive">
            <h4 class="font-weight-bold text-center text-coekku">Donator</h4>
            <p class="text-center text-muted"><small>ทุกคนที่บริจาคจะได้รับ <text class="rainbow font-weight-bold">ชื่อสีรุ้ง</text> (ถาวร)</small><br><small><text class='font-weight-italic text-coekku'>ตัดรอบทุก ๆ วันที่ 26 เวลา 23:59:59 ของทุกเดือน</text></small></p>
                <table class="table table-sm table-hover w-100" id="submissionTable" style="max-height: 60vh;">
                    <thead>
                        <tr class="text-nowrap me">
                            <th scope="col" class="font-weight-bold text-coekku">User</th>
                            <th scope="col" class="font-weight-bold text-coekku">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="text-nowrap">
                        <?php
                            $json = json_decode(file_get_contents("https://api.p0nd.dev/p0ndja/donation_grader?onlyThisMonth"), true);
                            $amount = 0;
                            foreach ($json as $j) {
                                $time = strtotime($j['timestamp']); $month = date('m', $time);
                                $curMonth = date('m', time());
                                if ($month == $curMonth) {
                                    echo "<tr><th scope='row'>".$j['name']."</th><td>".$j['value']." ฿</td></tr>";
                                    $amount += $j['value'];
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php   $val = ($amount / 150)*100;
                    $valMsg = $val > 100 ? "<text class='font-weight-bold rainbow'>".number_format((float) $val, 2, '.', '')."%</text>" : number_format((float) $val, 2, '.', '') . "%"; ?>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-coekku" role="progressbar" style="width: <?php echo $val;?>%" aria-valuenow="<?php echo $val;?>" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <p class="text-center"><small class="text-muted"><?php echo $amount; ?>/150 THB (<?php echo $valMsg; ?>)</small></p>
        </div>
    </div>
</div>
