<?php

return function ($pages, $active_page) {

    $html = "<nav class=\"mt-2\">
        <ul class=\"nav nav-pills nav-sidebar flex-column\" data-widget=\"treeview\" role=\"menu\" data-accordion=\"false\"> ";
    foreach ($pages as $page=>$name) {
        $active = $active_page == $page ? 'active' : '';
        $html .= " <li class='nav-item'>
                <a href='/index.php?p={$page}' class='nav-link $active'>
                  <i class='far fa-bookmark'></i>
                  <p>{$name}</p>
                </a>
              </li>";
    }
    $html .= "         
        </ul>
      </nav>";
    return $html;
};
