<?php

return function ($page_name, $total_pages, $pageno) {
    return " 
 <ul class='pagination' style='text-align: center; margin-top: 0; color: black'>
    <li  style='margin-right: 5px; '>
        <a class='btn btn-default ".(($pageno <= 1) ? 'disabled' : '')."' href='?p=$page_name&pageno=1'>{{QUEST_BTN_FIRST}}</a>
    </li>
    <li class='' style='margin-right: 5px; '>
       <a class='btn btn-default ".(($pageno <= 1) ? 'disabled' : '')."' href='?p=$page_name&pageno=".(($pageno <= 1) ? '#' : ($pageno - 1))."'>{{QUEST_BTN_PREV}}</a>
    </li>
    <li class='".(($pageno >= $total_pages) ? 'disabled' : '')."' style='margin-right: 5px; '>
        <a class='btn btn-default ".(($pageno >= $total_pages) ? 'disabled' : '')."' href='?p=$page_name&pageno=".(($pageno >= $total_pages) ? '#' : ($pageno + 1))."'>{{QUEST_BTN_NEXT}}</a>
    </li>
    <li class=''>
        <a class='btn btn-default ".(($pageno >= $total_pages) ? 'disabled' : '')."' href='?p=$page_name&pageno=$total_pages'>{{QUEST_BTN_LAST}}</a>
    </li>
 </ul>";
};