<?php

class html
{
    protected $noties = [];

    public  $listHouses = '';
    public  $listStreets = '';
    public  $listCities = '';


    function formDate($name, $value)
    {
        $html = "  <input type='text' name='$name' class='form-control'   id='$name'>

	<script type='text/javascript'>
    $(function () {
      $('#$name').datetimepicker({language: 'ru', pickTime: false, defaultDate: '$value'});
	});
	</script>
		";
        return $html;
    }

    function formDateTime($name, $value)
    {
        $html = "  <input type='text' name='$name' class='form-control'   id='$name'>

	<script type='text/javascript'>
    $(function () {
      $('#$name').datetimepicker({language: 'ru', pickTime: true, defaultDate: '$value'});
	});
	</script>
		";
        return $html;
    }

    function formButton($desc, $name = '', $value = '')
    {
        $html = "<button  type='submit' name='$name'  value='$value' style='height: 28px; padding-top: 2px; ' class='btn btn-primary'>$desc</button>";
        return $html;
    }

    function formSelect($arr, $name, $value, $submit = 0)
    {
        if ($submit == 1) $s = "onchange=\"this.form.submit()\""; else $s = '';
        $html = "<select name='$name'  class='form-control' $s style='height: 28px; padding-top: 4px; max-width: 230px'>";
        foreach ($arr as $k => $v) {
            if ("$k" === "$value") $sel = 'SELECTED'; else $sel = '';

            $html .= "<OPTION value='$k' $sel >$v";
        }
        $html .= "</select>";
        return $html;
    }

    function formCheckbox($name, $value = '0', $style = '')
    {
        if ($value == 1) $ch = 'checked'; else $ch = '';
        return "<input class='$style' $ch type=\"checkbox\" onClick=\"document.getElementById('$name').value = this.checked ? 1 : 0;\">
<input type='hidden' name='$name' id='$name' value='$value'>";
    }

    function formInput($name, $value, $variable = false)
    {
        if ($variable) {
            if (isset($variable['placeholder'])) $placeholder = "placeholder='" . $variable['placeholder'] . "'"; else $placeholder = '';
            if (isset($variable['required'])) $requ = "required"; else $requ = '';
            if (isset($variable['class'])) $class = "class='" . $variable['class'] . "'"; else $class = '';
            if (isset($variable['style'])) $style = "style='" . $variable['style'] . "'"; else $style = '';
            if (isset($variable['inset'])) $inset = $variable['inset']; else $inset = '';
            if (isset($variable['pattern'])) $pattern = $variable['pattern']; else $pattern = '';
        }
        return "<input  name='$name' value='$value' $pattern $placeholder $requ $class style='height: 28px' $inset>";
    }

    function tableMain($arr, $class)
    {
        $table = "<table class='$class'><tr>";
        foreach ($arr['head'] as $v) $table .= "<th>" . $v;
        unset($arr['head']);
        foreach ($arr as $row) {
            if (isset($row['color'])) {
                $color = "style='" . $row['color'] . "'";
                unset($row['color']);
            } else $color = '';
            $table .= "<tr $color>";
            foreach ($row as $cell)
                $table .= "<td>" . $cell;
        }
        $table .= "</table>";
        return $table;
    }

    function getHouses($city = 0, $street = 0, $house = 0, $sql)
    {
        $cities = [];
        $streets = [];
        $houses = [];
        $data = dbConnPDO()->query("SELECT c.name city, c.id cid, s.name street, s.id sid, h.name house, h.id hid
                                FROM addr_houses h 
                                JOIN addr_streets s on s.id = h.street
                                JOIN addr_cities c on c.id = s.city
                                WHERE h.group_id in (".join(",",\envPHP\service\PSC::getAllowedHouseGroups()).")
                                order by 1,3,5");
        foreach($data->fetchAll() as $d) {
            $cities[$d['cid']] = $d['city'];
            $streets[$d['cid']][$d['sid']] = $d['street'];
            $houses[$d['cid']][$d['sid']][$d['hid']] = $d['house'];
        }
        $listCity = "<SELECT class='form-control' name='city' onChange='submit()'><OPTION value=0>Не выбрано</OPTION>";
        if (count($cities) != 0) foreach ($cities as $k => $v) {
            if ($k == $city) $sel = "SELECTED"; else $sel = '';
            $listCity .= "<OPTION value='$k' $sel>$v</OPTION>";
        }
        $listCity .= "</select>";

        $liststreet = "<SELECT class='form-control' name='street' onchange='submit()'><OPTION value=0>Не выбрано</OPTION>";
        if (isset($streets[$city]) && count($streets[$city]) != 0) foreach ($streets[$city] as $k => $v) {
            if ($k == $street) $sel = "SELECTED"; else $sel = '';
            $liststreet .= "<OPTION value='$k'$sel>$v</OPTION>";
        }
        $liststreet .= "</select>";

        $listhouse = "<SELECT class='form-control' name='house' onChange='submit()'><OPTION value=0>Не выбрано</OPTION>";
        if (isset($houses[$city][$street]) && count($houses[$city][$street]) != 0) foreach ($houses[$city][$street] as $k => $v) {
            if ($k == $house) $sel = "SELECTED"; else $sel = '';
            $listhouse .= "<OPTION value='$k' $sel>$v</OPTION>";
        }
        $listhouse .= "</select>";

        $this->listHouses = $listhouse;
        $this->listStreets = $liststreet;
        $this->listCities = $listCity;
    }

    function addNoty($type, $message)
    {
        $this->noties[] = [
            'type' => $type,
            'message' => addslashes($message),
        ];
    }
    function addBackendNoty($type, $message) {
        if(!isset($_SESSION['backend_notifications'])) {
            $_SESSION['backend_notifications'] = [];
        }
        $_SESSION['backend_notifications'][] = [
            'type' => $type,
            'message' => $message,
        ];
        return $this;
    }
    function __destruct()
    {
        // echo "<script>const Noty = require(\"noty\");</script>";
        if(isset($_SESSION['noties'])) {
            $this->noties = array_merge($_SESSION['noties'], $this->noties);
        }
        $_SESSION['noties'] = [];

        if(isset($_SESSION['backend_notifications'])) {
            $_SESSION['noties'] = array_merge($_SESSION['backend_notifications'], $_SESSION['noties']);
            $_SESSION['backend_notifications'] = [];
        }

        if (count($this->noties) != 0) {
            foreach ($this->noties as $note) {
                echo <<<HTML
<script>
  var note = new Noty({
       type: '{$note['type']}',
       layout: 'topRight',
       theme: 'metroui',
       text: '{$note['message']}',
       timeout: '4000',
       progressBar: true,
       closeWith: ['click'], 
  });
  note.show();
</script>
HTML;
            }
        }
        echo "</body>";
        echo "</html>";
    }
}
