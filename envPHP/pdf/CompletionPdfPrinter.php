<?php


namespace envPHP\pdf;


class CompletionPdfPrinter
{
    protected $mpdf = null;
    protected $template ="";
    protected $variables = [];
    protected $completionRows = [];
    protected $output = "" ;
    function __construct(\Mpdf\Mpdf $mpdf)
    {
        $this->mpdf = $mpdf;
    }
    function setTemplate($template) {
        $this->template = $template;
        return $this;
    }
    function setVariables($variables) {
        $this->variables = $variables;
        return $this;
    }

    function addCompletionRow($name, $agreement, $count, $price) {
        if(!$count) {
            $count = 0;
        }
        if(!$price) {
            $price = 0;
        }
        $this->completionRows[] = [
          'name' => $name,
          'agreement' => $agreement,
          'count' => $count,
          'price' => $price,
          'amount' => $count * $price,
        ];
        return $this;
    }

    function prepareTemplate() {
        $variables = $this->variables;
        $template = $this->template;

        $variables['COMPLETIONS'] = "";
        foreach ($this->completionRows as $num=>$val) {
            $number = $num+1;
            $variables['COMPLETIONS'] .= "
            <tr>
                <td valign=\"top\" style=\"border: 1px solid black; padding: 2px;\">
                    {$number}
                </td>
                <td valign=\"top\" style=\"border: 1px solid black; padding: 2px;\">
                    {$val['name']}
                </td>
                <td  valign=\"top\" style=\"border: 1px solid black; padding: 2px;\">
                    {$val['agreement']}
                </td>
                <td   valign=\"top\" style=\"border: 1px solid black; padding: 2px;\">
                    {$val['count']}
                </td>
                <td  valign=\"top\" style=\"border: 1px solid black; padding: 2px;\">
                    {$val['price']}
                </td>
                <td  valign=\"top\" style=\"border: 1px solid black; padding: 2px;\">
                    {$val['amount']}
                </td>
            </tr>
            ";
        }

        foreach ($variables as $key => $value) {
            $template = preg_replace("/({{{$key}}})/", $value, $template);
        }

        $this->output = $template;
        return $this;
    }
    function save($path) {
        $this->mpdf->WriteFixedPosHTML($this->output, 15,10,180,297);
        $this->mpdf->Output($path, 'F');
        return $this;
    }
    function outputFixedHTML() {
        $this->mpdf->WriteFixedPosHTML($this->output, 15,10,180,297);
        $this->mpdf->Output('print.pdf', 'I');
        return $this;
    }
}