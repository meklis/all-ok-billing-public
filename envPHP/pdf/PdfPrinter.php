<?php

namespace envPHP\pdf;

class PdfPrinter {
    protected $mpdf = null;
    protected $template ="";
    protected $variables = [];
    protected $output = "";
    protected $count = 1;
    protected $disabledDelimiter = false;
    function __construct(\Mpdf\Mpdf $mpdf)
    {
        $this->mpdf = $mpdf;
    }
    function setTemplate($template) {
        $this->template = $template;
        return $this;
    }
    function disableDelimiter() {
        $this->disabledDelimiter = true;
        return $this;
    }
    function setVariables($variables) {
        $this->variables = $variables;
        return $this;
    }
    function setTableColls($count = 1) {
        $this->count = $count;
        return $this;
    }

    function write($array) {
        $this->output = "<table  ><tr>";
        $count = 0;
        foreach ($array as $element) {
            $variables = $this->variables;
            foreach ($element as $key => $value) {
                $variables[$key] = $value;
            }
            $template = $this->template;
            foreach ($variables as $key => $value) {
                $template = preg_replace("/({{{$key}}})/", $value, $template);
            }
            if($count >= $this->count) {
                $this->output .= "</tr><tr>";
                $count = 0;
            }
            $count++;
            if($this->disabledDelimiter) {
                $this->output .= "<td style='padding-bottom: 5px;  '>$template</td>";
            } else {
                $this->output .= "<td style='padding-bottom: 10px;   '>$template</td>";
            }
        }
        $this->output .= "</tr></table>";

        return $this;
    }
    function outputFixedHTML() {
        $this->mpdf->WriteFixedPosHTML($this->output, 15,10,180,297);
        $this->mpdf->Output('print.pdf', 'I');
    }
    function outputHTML() {
        $this->mpdf->WriteHTML($this->output);
        $this->mpdf->Output('print.pdf', 'I');
    }
}
