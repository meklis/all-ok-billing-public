<?php

namespace envPHP\MultiSwitcher;

use SwitcherCore\Switcher\Core;
use SwitcherCore\Switcher\CoreConnector;

class SearchFDB
{
    /**
     * @var CoreConnector
     */
    protected $connector;

    /**
     * @var MultiSwitcher
     */
    protected $multiSwitcher;

    function __construct(CoreConnector $connector, MultiSwitcher $switcher)
    {
        $this->connector = $connector;
        $this->multiSwitcher = $switcher;
    }



}