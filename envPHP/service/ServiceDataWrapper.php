<?php


namespace envPHP\service;


abstract  class ServiceDataWrapper
{
    /**
     * @var array example - ['description' => ['descr', 'string']], where description - name of field for get/update, descr - name of field in SQL table, string - type of field
     */
    protected $editableFields;
    protected $sql;
    function getEditableFields() {
        return array_keys($this->editableFields);
    }
    function __construct()
    {
        $this->sql = dbConnPDO();
    }

    /**
     * Wrap element for response.
     * Replace parameter names
     * @param $array
     * @return array
     */
    function wrapForResponse($array) {
        $response = [];
        foreach ($array as $kname=>$kval) {
            if(isset($this->editableFields[$kname]) && $this->editableFields[$kname][1] == 'bool') {
                $response[$kname] = $kval ? true : false;
            } else {
                $response[$kname] = $kval;
            }
        }
        return $response;
    }
    function wrapArrayForResponse($array) {
        $response = [];
        foreach ($array as $k=>$v) {
            $response[] = $this->wrapForResponse($v);
        }
        return $response;
    }

    /**
     * @param $params array Array must be as key=>value array
     * @return array example of return ['descr = ?', ['value of description'] ]
     */
    function wrapForUpdate($params) {
        $fieldSQL = "";
        $arguments = [];
        if(count($params) == 0) {
            throw new \InvalidArgumentException("Not received params to update");
        }
        foreach ($params as $pName => $pVal) {
            if(!isset($this->editableFields[$pName])) {
                throw new \InvalidArgumentException("Unknown field with name $pName for updating");
            }
            list($sqlName, $type) = $this->editableFields[$pName];
            switch ($type) {
                case 'string':
                    if(!$pVal) {
                        $pVal = '';
                    }
                    break;
                case 'int':
                    if(!$pVal) {
                        $pVal = 0;
                    }
                    break;
                case 'bool':
                    $pVal = $pVal ? 1 : 0;
                    break;
            }
            $fieldSQL .= ", `{$sqlName}` = ? ";
            $arguments[] = $pVal;
        }
        $fieldSQL = trim($fieldSQL, ',');
        return [$fieldSQL, $arguments];
    }
}