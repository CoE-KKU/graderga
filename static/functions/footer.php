<footer id="footer" class="footer">
    <div class="container-fluid">
        <div class="text-center mb-3">
            <hr style="opacity: 0.3;">
            <b>Grader.ga</b> - Made with <b style="color: salmon; ">Meow🐾</b> by <a href="https://github.com/p0ndja/" class="font-weight-bold" target="_blank">p0ndja</a> & <a href="https://github.com/Nepumi-Jr/" class="font-weight-bold" target="_blank">Nepumi</a>
            <br><sup><small class="text-muted" style="opacity:0.75;"><?php $target = "../version.txt"; if (file_exists($target)) echo "Version " . fread(fopen($target, "r"),filesize($target)); ?> • <?php $end_time = microtime(TRUE); $time_taken =($end_time - $start_time)*1000; $time_taken = round($time_taken,2); echo 'Page generated in ' . $time_taken . ' ms.';?> • Visual from <a href="https://www.flaticon.com/" title="Flaticon" target="_blank">Flaticon</a>, <a href="https://www.freepik.com/" title="Freepik" target="_blank">Freepik</a> and <a href="https://fontawesome.com/" title="Fontawesome" target="_blank">Fontawesome</a></small></sup>
        </div>
    </div>
</footer>
<script>hljs.initHighlightingOnLoad();</script>
<?php if (!isset($_SESSION['loadAnnouncement'])) { $_SESSION['loadAnnouncement'] = true; ?>
    <script>//$('#announcementPopup').modal('show');</script>
<?php } ?>
<script>
    $('input[type=text], input[type=password], input[type=email], input[type=url], input[type=tel], input[type=number], input[type=search], input[type=date], input[type=time], textarea').each(function (element, i) {
        if ((element.value !== undefined && element.value.length > 0) || $(this).attr('placeholder') !== undefined) {
            $(this).siblings('label').addClass('active');
        } else {
            $(this).siblings('label').removeClass('active');
        }
        $(this).trigger("change");
    });
    
    // Tooltips Initialization
    $(document).ready(function () {
        if ($('.mdb-select')[0]) $('.mdb-select').materialSelect();
        $('[data-toggle="tooltip"]').tooltip();
        $('.btn-floating').unbind('click');
        $('.fixed-action-btn').unbind('click');
        pdfNiceLook();
        attachFooter();
        checkResult();
    });

    function checkResult() {
        setTimeout(function() {
            ($('[data-wait=true]')?.first(function(index) {
                var subID = $(this).data('sub-id');
                $(this).load('../pages/prob_result.php?id='+subID+"&score");
                if ($(this).html().indexOf("รอผลตรวจ...") === -1) {
                    $(this).removeAttr("data-wait");
                    console.log("Finished Juding " + subID + " -> " + $(this).html());  
                } else {
                    console.log("Waiting for Juding " + subID);  
                }
            }));
            checkResult();
        }, 1500);
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightBlock(block);
        });
    });

    // create an Observer instance
    const resizeObserver = new ResizeObserver(entries => attachFooter());

    // start observing a DOM node
    resizeObserver.observe(document.body);

    if (document.getElementById("problemDetails"))
        resizeObserver.observe(document.getElementById("problemDetails"));


    function pdfNiceLook() {
        //console.log($("#problemDetails").height());
        var targetHeight = 650;
        if ($("#problemDetails").height() > 650)
            targetHeight = $("#problemDetails").height();
        $("#pdfViewer").height(targetHeight-30);
    }

    function attachFooter() {
        //console.log($(document.body).height() + " | " + $(window).height());
        if ($(document.body).height()*1.11 < $(window).height()) {
            $('#footer').attr('style', 'position: fixed!important; bottom: 0px;');
        } else {
            $('#footer').removeAttr('style');
        }
        pdfNiceLook();
    }

    $('.dropdown-menu').find('form').click(function (e) {
        e.stopPropagation();
    });

    $('.carouselsmoothanimated').on('slide.bs.carousel', function(e) {
        $(this).find('.carousel-inner').animate({
            height: $(e.relatedTarget).height()
        }, 500);
    });
</script>
<?php mysqli_close($conn); ?>