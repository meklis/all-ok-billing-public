<?php
use envPHP\classes\std;

require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if(!\envPHP\service\PSC::isPermitted('question_report_change')) {
    pageNotPermittedAction();
}

$form = [
    'id' => 0,
];

std::Request($form);

$cfg = getGlobalConfigVar('CERT_OF_COMPLETION');
$isSubscribed = dbConn()->query("SELECT id FROM question_responses WHERE id = '{$form['id']}' and cert_subscribed is not null")->num_rows > 0  ? true : false;
$questionId = dbConn()->query("SELECT question FROM question_responses WHERE id = '{$form['id']}'")->fetch_assoc()['question'];
$pdfUrl = "/load_pdf?file_path=question_certs/{$form['id']}.pdf";

if($isSubscribed) {
    $pdfUrl = "/load_pdf?file_path=question_certs_subscribed/{$form['id']}.pdf";
}

?>
<?=tpl('head', ['title'=>""])?>
    <style>
        #pdf-canvas {
            transform: scale(1);
        }
    </style>
<div class="modal" id="subscribeModal">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Подписание акта выполненных работ</h4>
            </div>

            <!-- Modal body -->
            <div class="modal-body">
                <div id="subscribe-content" style="  background: #F0F0F0; overflow: hidden; border: 1px solid lightgrey; " >
                    <canvas  width="600" height="400" id='subscribe-canvas' >Обновите браузер</canvas>
                </div>

                Нарисуйте вашу подпись в окошке и нажмите "Подписать"<br>
                Если необходимо очистить полотно - нажмите "Очистить"
            </div>

            <!-- Modal footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-warning"   onclick="clearSubscribe(); return false;">Очистить</button>
                <button type="button" class="btn btn-success btn-lg" onclick="save(); return false;">Подписать</button>
            </div>

        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-xs-12 col-md-12 col-lg-2"></div>
    <div class="col-sm-12 col-xs-12 col-md-12 col-lg-8">
        <div class="x_panel">
            <div class="x_title">
                <h2>Акт выполненных работ #<?=$form['id']?></h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12 col-xs-12 col-lg-12">
                        <?php if(!$isSubscribed) echo "<button class='btn btn-lg btn-success' data-toggle=\"modal\" data-target=\"#subscribeModal\">Подписать</button>"?>
                        <a href="<?=$pdfUrl?>" target="_blank" class="btn btn-primary" style="float: right">Скачать в PDF</a>
                        <a href="/abonents/question_response?id=<?=$questionId?>" class="btn btn-default"  style="float: right">Вернуться к отчету</a>
                        <div id="pdf-main-container" >
                            <div id="pdf-contents" style="overflow: hidden; width: 100%" >
                                <canvas id="pdf-canvas" ></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-12 col-xs-12 col-md-12 col-lg-1"></div>
</div>

<script src="/res/pdf-to-png/pdf.js"></script>
<script src="/res/pdf-to-png/pdf.worker.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/3.6.2/fabric.js"></script>
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
            "url": "<?=getGlobalConfigVar('BASE')['api2_front_addr']?>/v2/private/customers/question/report/sign_cert_of_completion" ,
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
<?=tpl('footer')?>