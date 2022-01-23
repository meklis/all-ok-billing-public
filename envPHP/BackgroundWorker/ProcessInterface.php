<?php


namespace envPHP\BackgroundWorker;


interface ProcessInterface
{
        function __construct($arguments);
        function run();
}

