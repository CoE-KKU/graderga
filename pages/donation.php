<div class="container mb-3" style="padding-top: 88px; min-height: 100vh !important;" id="container">
    <h1 class="display-4 font-weight-bold text-center text-coekku">Donation</h1>
    <div class="row">
        <div class="col-12 col-md-8">
            <div class="d-none d-md-block" style="padding-top: 8vh;"></div>
            <div class="d-flex justify-content-center"><img src="https://i.ibb.co/tb6g1hf/kbankqr.jpg" class="mb-3" width="300" /></div>
            <p class="text-center"><b>Promptpay</b> : <code style="color: blue;">090-8508007</code></p>
            <h5 class="text-center font-weight-bold">Palapon Soontornpas / พลภณ สุนทรภาส</h5>
            <p class="text-center">ส่งเอกสารการโอนเงิน Donate ได้ที่
                <br>Email: <a href="mailto:palapon@kkumail.com">palapon@kkumail.com</a>
                <br>Messenger: <a href="https://m.me/p0ndja" target="_blank">Palapon Soontornpas</a>
            </p>
        </div>
        <div class="col-12 col-md-4">
        <div class="d-none d-md-block" style="padding-top: 5vh;"></div>
            <div class="table-responsive">
            <h4 class="font-weight-bold text-center text-coekku">Donator</h4>
            <p class="text-center text-muted"><small>ทุกคนที่บริจาคจะได้รับ <text class="rainbow font-weight-bold">ชื่อสีรุ้ง</text> (ถาวร)</small></p>
                <table class="table table-sm table-hover w-100" id="submissionTable" style="max-height: 60vh;">
                    <thead>
                        <tr class="text-nowrap me">
                            <th scope="col" class="font-weight-bold text-coekku">User</th>
                            <th scope="col" class="font-weight-bold text-coekku">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="text-nowrap">
                        <?php
                            $json = json_decode(file_get_contents("https://api.p0nd.dev/p0ndja/donation_grader"), true);
                            $amount = 0;
                            foreach ($json as $j) {
                                $time = strtotime($j['timestamp']); $date = date('d M Y', $time);                                
                                echo "<tr><th scope='row'>".$date."</th><td>".$j['name']."</td><td>".$j['value']." ฿</td></tr>";
                                $amount += $j['value'];
                            }
                        ?>
                    </tbody>
                </table>
            </div>
            <p class="text-center mt-3">รวมยอดเงินทั้งสิ้น <?php echo $amount; ?> บาท</p>
        </div>
    </div>
</div>
