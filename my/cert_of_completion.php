<?php

use envPHP\classes\std;

require(__DIR__ . "/../envPHP/load.php");

session_start();

$form = [
  'id' => '',
];
std::Request($form);

$pdfUrl = getGlobalConfigVar('BASE')['my_addr'] . "/pdf_cert_of_completion.php?id={$form['id']}";
?>
<html>
<head>
    <title>Акт выполненных работ #<?=$form['id']?></title>
<link href="<?=getGlobalConfigVar('BASE')['service_addr']?>/res/css/bootstrap.min.css" rel="stylesheet">
<script src="<?=getGlobalConfigVar('BASE')['service_addr']?>/res/js/jquery.min.js"></script>
<script src="<?=getGlobalConfigVar('BASE')['service_addr']?>/res/js/bootstrap.min.js"></script>
<script src="<?=getGlobalConfigVar('BASE')['service_addr']?>/res/pdf-to-png/pdf.js"></script>
<script src="<?=getGlobalConfigVar('BASE')['service_addr']?>/res/pdf-to-png/pdf.js"></script>
<script src="<?=getGlobalConfigVar('BASE')['service_addr']?>/res/pdf-to-png/pdf.worker.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/3.6.2/fabric.js"></script>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-sm-4 col-md-3 col-lg-5 col-xs-12"></div>
        <div class="col-sm-4 col-md-6 col-lg-2 col-xs-12"><a href="pdf_cert_of_completion.php?id=<?=$form['id']?>" class="btn btn-default btn-block" style="margin: 10px">Скачать в PDF</a></div>
        <div class="col-sm-4 col-md-3 col-lg-5 col-xs-12"></div>
    </div>

    <div class="row">
        <div  class="col-sm-12 col-md-12 col-xs-12 col-lg-12">
            <div id="pdf-main-container" style="border: 1px solid #C0C0C0; margin: 5px;">
                <div id="pdf-contents" style="overflow: hidden; width: 100%" >
                    <canvas id="pdf-canvas" ></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var subscribe_canvas = this.__canvas = new fabric.Canvas('subscribe-canvas', {
        isDrawingMode: true
    });
    subscribe_canvas.freeDrawingBrush.width = 10;
    subscribe_canvas.freeDrawingBrush.color = '#204ac8';

    function save(){
        var image = document.getElementById("subscribe-canvas").toDataURL("image/png");
        var formData = {
            sign:  image,
            id: <?=$form['id']?>
        };

        $.ajax({
            "url": "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>/question/report/sign_cert_of_completion" ,
            "method": "POST",
            "dataType": 'json',
            "data": JSON.stringify(formData),
            "headers": {
                "X-Auth-Key": getApiToken(),
            },
        }).done(function (data) {
            window.location.reload();
        }).error(function (data) {
            console.log(data);
            alert(data.responseJSON.error.description);
        });
    }
    function clearSubscribe() {
        subscribe_canvas.clear();
    }



    var
        __CANVAS = $('#pdf-canvas').get(0),
        __PDF_DOC = null,
        __CANVAS_CTX = __CANVAS.getContext('2d');

    // resize canvas
    function resize() {
        __CANVAS_CTX.canvas.width = document.getElementById("pdf-contents").offsetWidth;
        __CANVAS_CTX.canvas.height = document.getElementById("pdf-contents").offsetHeight;
    }

    function getPage() {
        __PDF_DOC.getPage(1).then(function(page) {

            // As the canvas is of a fixed width we need to set the scale of the viewport accordingly
            var scale_required = __CANVAS.width / page.getViewport(1).width;

            // Get viewport of the page at required scale
            var viewport = page.getViewport(scale_required);

            // Set canvas height
            __CANVAS.height = viewport.height;

            var renderContext = {
                canvasContext: __CANVAS_CTX,
                viewport: viewport
            };

            // Render the page contents in the canvas
            page.render(renderContext).then(function() {

            });
        });
    }

    function showPDF(pdf_url) {
        PDFJS.getDocument({ url: pdf_url }).then(function(pdf_doc) {
            __PDF_DOC = pdf_doc;
            pdf_doc.getPage(1).then(function(page) {

                // As the canvas is of a fixed width we need to set the scale of the viewport accordingly
                var scale_required = __CANVAS.width / page.getViewport(1).width;

                // Get viewport of the page at required scale
                var viewport = page.getViewport(scale_required);

                // Set canvas height
                __CANVAS.height = viewport.height;

                var renderContext = {
                    canvasContext: __CANVAS_CTX,
                    viewport: viewport
                };

                // Render the page contents in the canvas
                page.render(renderContext).then(function() {

                });
            });
        }).catch(function(error) {
            alert(error.message);
        });
    }

    $(window).resize(function() {
        resize();
        getPage();
    });

    window.addEventListener('load', function() {
        resize();
        showPDF("<?=$pdfUrl?>");
    });




</script>
</body>
</html>