<?php
require($_SERVER['DOCUMENT_ROOT'] . "/include/load.php");
init();


if(!\envPHP\service\PSC::isPermitted('employees_group')) {
    pageNotPermittedAction();
}
$form = [
    'id' => 0,
    'permissions' => [],
    'allowed' => [],
    'action' => '',
];
$ht = [
    'groups' => [],
];


\envPHP\classes\std::Request($form);

$grpsth = dbConnPDO()->prepare("SELECT id, position, `show` FROM emplo_positions WHERE id = ?");
$grpsth->execute([$form['id']]);
$groupInfo = $grpsth->fetch();
$permissions = new \envPHP\service\PermissionControl($form['id']);



if($form['action'] == 'save') {
    $permissions->setGroupPermissions($form['permissions'])->save();
    $permissions->setAllowedHouseGroups($form['allowed']);
}

$houseGroupsData = dbConnPDO()->query("SELECT id, name FROM addr_groups order by 2")->fetchAll(PDO::FETCH_ASSOC);
$allowedGroupsData = [];
foreach ($permissions->getAllowedHouseGroups() as $id) {
    foreach ($houseGroupsData as $group) {
        if($group['id'] === $id) {
            $allowedGroupsData[] = $group;
            break;
        }
    }
}
$allowedGroups = json_encode($allowedGroupsData, JSON_UNESCAPED_UNICODE);
$houseGroups = json_encode($houseGroupsData, JSON_UNESCAPED_UNICODE);

if(!$groupInfo['position']) {
    $HTML = tpl('head', ['title' =>'']);
    $HTML .= "<h3 align='center'>Группа не найдена</h3>";
} else {
    $HTML = tpl('head', ['title' => "Контроль прав для {$groupInfo['position']}"]);
}


$HTML .= <<<HTML
    <form method="POST">
<div class="row" >
    <div class="col-sm-2">
           <a href="/users/groups"><button class="btn  btn-block" type="button">Вернуться к группам</button></a>
    </div>
 
    <div class="col-sm-3">
           <button class="btn btn-primary btn-block" type="submit" name="action" value="save">Сохранить</button>
    </div>
 
    <div class="col-sm-2">
           <button class="btn  btn-danger btn-block" type="reset">Сбросить все</button>
    </div>
</div>
<div class="row">
HTML;

$groupClassId = 0;
foreach ($permissions->getPermissionsTemplate() as $groupName => $perms) {
    $groupClassId +=1;
    $checkAll = 'checked';
    foreach ($perms as $perm) {
        if(!in_array($perm['key'], $permissions->getGroupPermissions())) {
            $checkAll='';
        }
    }
    $permsHTML = "";
    foreach ($perms as $perm) {
        $checked = $perm['checked'] ? 'checked' : '';
        $permsHTML .= "
            <div class='row'> 
            <div class='col-sm-2 col-lg-2 col-md-2 col-xs-2' style='max-width: 27px;'> <input class='group_block_{$groupClassId}' type='checkbox' $checked name='permissions[]' style='height: 20px; width: 20px' value='{$perm['key']}'></div>
            <div class='col-sm-10 col-lg-10 col-md-10 col-xs-10'>{$perm['name']}</div>
            </div>
        ";
    }
    $HTML .= <<<HTML
    <div class="col-sm-4 col-xs-6 col-md-4 col-lg-4">
        <div class="x_panel">
            <div class="x_title">
                <div class="row">
                    <div class='col-sm-2 col-lg-2 col-md-2 col-xs-2' style='max-width: 35px;'> <input id='group_name_{$groupClassId}' type='checkbox' $checkAll onclick="checkUnCheck('group_block_{$groupClassId}', 'group_name_{$groupClassId}')"></div>
                   <div class='col-sm-10 col-lg-10 col-md-10 col-xs-10'><h2>{$groupName}</h2></div>
                </div>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <div class="row">
                    <div class="col-sm-12 col-xs-12 col-lg-12">
                        {$permsHTML}
                    </div>
                </div>
            </div>
        </div>
    </div>
HTML;
}

$HTML .= <<<HTML
</div>
<div class="row">
<div class="col-lg-2 col-sm-2 col-md-2"></div>
<div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Закрепление разрешенных для просмотра груп</h2>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">
                            <table style="width: 100%">
                                <tr>
                                    <td style="width: 45%">
                                        Список групп домов 
                                        <select size="20" class='form-control' style="width: 100%" multiple id="list_groups">
                                        </select>
                                    <td valign="center" align="center" style="width: 10%">
                                        <button class="btn btn-default"  
                                                style="margin: 3px;"
                                                onclick="allowSelected(); return false;"
                                                > >
                                        </button>
                                        <br>
                                        <button class="btn btn-default"  
                                                style="margin: 3px;"
                                                onclick="removeSelected(); return false;"
                                                > <
                                        </button>
                                        <br>
                                        <button class="btn btn-default"  
                                                style="margin: 3px;"
                                                onclick="allowAll(); return false;"
                                                > >>
                                        </button>
                                        <br>
                                        <button class="btn btn-default"  
                                                style="margin: 3px;"
                                                onclick="removeAll(); return false;"
                                                > <<
                                        </button>
                                    <td style="width: 45%">
                                        Разрешенные группы
                                        <select size="20" style="width: 100%" class='form-control' multiple
                                                name="" id="allowed_groups">
                                        </select>
                                        <select hidden name="allowed[]" multiple hidden="hidden" id="to_send"></select>
                            </table>
                        </div>
                    </div>
                </div>
                </div>         
</form>
<script> 

function checkUnCheck(className, checkId) {
   $('.'+className+':input:checkbox').each(function() { this.checked = isChecked(checkId) });
}

function isChecked(checkId) {
  var checkBox = document.getElementById(checkId);
  return checkBox.checked;
}

var housesGroups = {$houseGroups};
var allowedGroups = {$allowedGroups};

function buildAllowedHousesList() {
   var select = document.getElementById('allowed_groups');
    select.innerHTML = ''
    allowedGroups.forEach(e => {
        var opt = document.createElement('option');
            opt.value = e.id;
            opt.innerHTML = e.name;
            select.appendChild(opt);    
    })
}
function buildToSendList() {
   var select = document.getElementById('to_send');
    select.innerHTML = ''
    allowedGroups.forEach(e => {
        var opt = document.createElement('option');
            opt.value = e.id;
            opt.selected = '1'
            opt.innerHTML = e.name;
            select.appendChild(opt);    
    })
}
function buildHousesList() {
    var select = document.getElementById('list_groups');
    list = housesGroups.filter(function (l) {
        var allow = true
        allowedGroups.forEach(a => {
            if(a.id === l.id) {
                allow = false 
                console.log(a.id + " - in allowed, must be filtered")
            }          
        })
        return allow 
    })
    select.innerHTML = ''
    list.forEach(e => {
        var opt = document.createElement('option');
            opt.value = e.id;
            opt.innerHTML = e.name;
            select.appendChild(opt);    
    })
}
function buildLists() {
    buildHousesList()
    buildAllowedHousesList()
    buildToSendList()
}

function removeAll() {
    allowedGroups = []
    buildLists()
}

function allowAll() {
    allowedGroups = []
    housesGroups.forEach(e => {
        allowedGroups.push(e)
    })
    buildLists()
}

function allowSelected() {
    var select = document.getElementById('list_groups').selectedOptions;
    for (let i=0; i<select.length; i++) {
     allowedGroups.push({
            id: select[i].getAttribute('value'),
            name: select[i].innerHTML
        })
    }
    buildLists()
}

function removeSelected() {
    var selected = document.getElementById('allowed_groups').selectedOptions;
    var newList = []
    allowedGroups.forEach(e => {
        var addToList = true 
        for (let i=0; i < selected.length; i++) {
             if(e.id === selected[i].getAttribute('value')) {
                 console.log(e)
                 addToList = false 
                 break
             }
        }
        if(addToList) {
            newList.push(e)
        }
    })
    allowedGroups = newList
    buildLists()
}

window.addEventListener('load', () => {
    buildLists()
})

</script>


HTML;

$HTML .= tpl('footer');


echo $HTML;