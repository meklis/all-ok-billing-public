<?php
$rank = 20;
$table = "<center><h3>Записей не найдено</h3></center>";
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();

if (!\envPHP\service\PSC::isPermitted('sys_question_reason')) {
    pageNotPermittedAction();
}

$form = [
    'save' => '',
    'ch' => [],

];

\envPHP\classes\std::Request($form);

if ($form['save']) {
    $error = false;
    foreach ($form['ch'] as $k => $v) {
        if ($v['name'] != '') {
            $psth = dbConnPDO()->prepare("INSERT INTO question_reason (id, created, `name`, display, reaction_time) 
VALUES (?,NOW(),?, ?, ?) 
ON DUPLICATE key UPDATE 
`name` = ?, display = ?, reaction_time = ?");
            $psth->execute([
                $k,
                $v['name'],
                $v['display'],
                $v['reaction_time'],
                $v['name'],
                $v['display'],
                $v['reaction_time'],
            ]);
        }
    }
    if ($error) {
        html()->addNoty('error', "Ошибка обновления: " . $sql->error . "");
    } else {
        html()->addNoty('success', "Успешно обновлено");
    }
}

$table = "<table class='table table-striped'>
            <tr>
                <th style='width: 300px'>Название
                <th style='width: 300px'>Описание
                <th style='width: 40px'>Время реакции";
$data = $sql->query("SELECT id, created, name, display, reaction_time FROM question_reason ORDER BY 1");
while ($d = $data->fetch_assoc()) {
    $displayOptions = '';
    foreach ([
                 'NO' => 'Отключено',
                 'YES' => 'Включено',
             ] as $k => $v) {
        $sel = $k === $d['display'] ? ' SELECTED ' : '';
        $displayOptions .= "<OPTION value='$k' $sel>$v</OPTION>";
    }
    $table .= "<tr>"
        . "<td><input class='form-control' name='ch[" . $d['id'] . "][name]' value='" . $d['name'] . "'>"
        . "<td><SELECT class='form-control' name='ch[{$d['id']}][display]'>{$displayOptions}</SELECT>"
        . "<td> <input class='form-control' name='ch[{$d['id']}][reaction_time]' value='{$d['reaction_time']}' type='number'>";
}
$max = $sql->query("SELECT max(id)+1 id FROM question_reason")->fetch_assoc()['id'];
$displayOptions = '';
foreach ([
             'NO' => 'Отключено',
             'YES' => 'Включено',
         ] as $k => $v) {
    $displayOptions .= "<OPTION value='$k' $sel>$v</OPTION>";
}
$table .= "<tr>"
    . "<td><input class='form-control' name='ch[{$max}][name]' value='' placeholder='Новый тип'>"
    . "<td><SELECT class='form-control' name='ch[{$max}][display]'>{$displayOptions}</SELECT>"
    . "<td> <input class='form-control' name='ch[{$max}][reaction_time]' value='' type='number' placeholder='10'>
</td></tr></table>";

?><?= tpl('head', ['title' => '']) ?>
    <form action="" method="post">
        <div class="row">
            <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Управление типом заявок</h2>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <div class="table-responsive-light">
                            <?= $table ?>
                        </div>
                        <div class="clearfix"></div>
                        <div class="divider-dashed"></div>
                        <button class="btn btn-primary" type="submit" name="save" value="save">Сохранить изменения</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

<?= tpl('footer') ?>