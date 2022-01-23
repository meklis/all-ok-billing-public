<?php


namespace envPHP\classes;


class Num2TextUa
{
    protected $prep_1_2 = [];
    protected $prep_1_19 = [];
    protected $prep_des = [];
    protected $prep_hang = [];
    protected $prep_namecurr = [];
    protected $prep_nametho = [];
    protected $prep_namemil = [];
    protected $prep_namemrd = [];
    protected function num2text_ua($num) {
        $num = trim(preg_replace('~s+~s', '', $num)); // отсекаем пробелы
        if (preg_match("/, /", $num)) {
            $num = preg_replace("/, /", ".", $num);
        } // преобразует запятую
        if (is_numeric($num)) {
            $num = round($num, 2); // Округляем до сотых (копеек)
            $num_arr = explode(".", $num);
            $amount = $num_arr[0]; // переназначаем для удобства, $amount - сумма без копеек
            if (strlen($amount) <= 3) {
                $res = implode(" ", $this->triada($amount)) . $this->currency($amount);
            } else {
                $amount1 = $amount;
                while (strlen($amount1) >= 3) {
                    $temp_arr[] = substr($amount1, -3); // засовываем в массив по 3
                    $amount1 = substr($amount1, 0, -3); // уменьшаем массив на 3 с конца
                }
                if ($amount1 != '') {
                    $temp_arr[] = $amount1;
                } // добавляем то, что не добавилось по 3
                $i = 0;
                foreach ($temp_arr as $temp_var) { // переводим числа в буквы по 3 в массиве
                    $i++;
                    if ($i == 3 || $i == 4) { // миллионы и миллиарды мужского рода, а больше миллирда вам все равно не заплатят
                        if ($temp_var == '000') {

                            $temp_res[] = '';
                        } else {
                            $temp_res[] = implode(" ", $this->triada($temp_var, 1)) . $this->getNum($i, $temp_var);
                        } # if
                    } else {
                        if ($temp_var == '000') {
                            $temp_res[] = '';
                        } else {
                            $temp_res[] = implode(" ", $this->triada($temp_var)) . $this->getNum($i, $temp_var);
                        } # if
                    } # else
                } # foreach
                $temp_res = array_reverse($temp_res); // разворачиваем массив
                $res = implode(" ", $temp_res) . $this->currency($amount);
            }
            if (!isset($num_arr[1]) || $num_arr[1] == '') {
                $num_arr[1] = '00';
            }
            return $res . ', ' . $num_arr[1] . ' коп.';
        } # if
    }

    protected function triada($amount, $case = null) {
        $count = strlen($amount);
        for ($i = 0; $i < $count; $i++) {
            $triada[] = substr($amount, $i, 1);
        }
        $triada = array_reverse($triada); // разворачиваем массив для операций
        if (isset($triada[1]) && $triada[1] == 1) { // строго для 10-19
            $triada[0] = $triada[1] . $triada[0]; // Объединяем в единицы
            $triada[1] = ''; // убиваем десятки
            $triada[0] = $this->prep_1_19[$triada[0]]; // присваиваем
        } else { // а дальше по обычной схеме
            if (isset($case) && ($triada[0] == 1 || $triada[0] == 2)) { // если требуется м.р.
                $triada[0] = $this->prep_1_2[$triada[0]]; // единицы, массив мужского рода
            } else {
                if ($triada[0] != 0) {
                    $triada[0] = $this->prep_1_19[$triada[0]];
                } else {
                    $triada[0] = '';
                } // единицы
            } # if
            if (isset($triada[1]) && $triada[1] != 0) {
                $triada[1] = $this->prep_des[$triada[1]];
            } else {
                $triada[1] = '';
            } // десятки
        }
        if (isset($triada[2]) && $triada[2] != 0) {
            $triada[2] = $this->prep_hang[$triada[2]];
        } else {
            $triada[2] = '';
        } // сотни
        $triada = array_reverse($triada); // разворачиваем массив для вывода
        $triada1 = [];
        foreach ($triada as $triada_) { // вычищаем массив от пустых значений
            if ($triada_ != '') {
                $triada1[] = $triada_;
            }
        } # foreach
        return $triada1;
    }

    private function currency($amount) {
        $last2 = substr($amount, -2); // последние 2 цифры
        $last1 = substr($amount, -1); // последняя 1 цифра
        $last3 = substr($amount, -3); //последние 3 цифры
        if(in_array($last1,[2,3,4])) {
            return ' '.$this->prep_namecurr[2];
        } elseif ($last1 == 1) {
            return ' '.$this->prep_namecurr[1];
        } else {
            return ' '.$this->prep_namecurr[3];
        }

    }

    private function getNum($level, $amount) {
        if ($level == 1) {
            $num_arr = null;
        } else if ($level == 2) {
            $num_arr = $this->prep_nametho;
        } else if ($level == 3) {
            $num_arr = $this->prep_namemil;
        } else if ($level == 4) {
            $num_arr = $this->prep_namemrd;
        } else {
            $num_arr = null;
        }
        if (isset($num_arr)) {
            $last2 = substr($amount, -2);
            $last1 = substr($amount, -1);
            if ((strlen($amount) != 1 && substr($last2, 0, 1) == 1) || $last1 >= 5) {
                $res_num = $num_arr[3];
            } // 10-19
            else if ($last1 == 1) {
                $res_num = $num_arr[1];
            } // для 1-цы
            else {
                $res_num = $num_arr[2];
            } // все остальные 2, 3, 4
            return ' ' . $res_num;
        } # if
        return ' ';
    }

    protected function __construct()
    {

        $this->prep_1_2[1] = "один";
        $this->prep_1_2[2] = "два";

        $this->prep_1_19[1] = "одна";
        $this->prep_1_19[2] = "дві";
        $this->prep_1_19[3] = "три";
        $this->prep_1_19[4] = "чотири";
        $this->prep_1_19[5] = "п'ять";
        $this->prep_1_19[6] = "шість";
        $this->prep_1_19[7] = "сім";
        $this->prep_1_19[8] = "вісім";
        $this->prep_1_19[9] = "дев'ять";
        $this->prep_1_19[10] = "десять";

        $this->prep_1_19[11] = "одинадцять";
        $this->prep_1_19[12] = "дванадцять";
        $this->prep_1_19[13] = "тринадцять";
        $this->prep_1_19[14] = "чотирнадцять";
        $this->prep_1_19[15] = "п'ятнадцять";
        $this->prep_1_19[16] = "шістнадцять";
        $this->prep_1_19[17] = "сімнадцять";
        $this->prep_1_19[18] = "вісімнадцять";
        $this->prep_1_19[19] = "дев'ятнадцять";


        $this->prep_des[2] = "двадцять";
        $this->prep_des[3] = "тридцять";
        $this->prep_des[4] = "сорок";
        $this->prep_des[5] = "п'ятдесят";
        $this->prep_des[6] = "шістдесят";
        $this->prep_des[7] = "сімдесят";
        $this->prep_des[8] = "вісімдесят";
        $this->prep_des[9] = "дев'яносто";

        $this->prep_hang[1] = "сто";
        $this->prep_hang[2] = "двісті";
        $this->prep_hang[3] = "триста";
        $this->prep_hang[4] = "чотириста";
        $this->prep_hang[5] = "п'ятсот";
        $this->prep_hang[6] = "шістсот";
        $this->prep_hang[7] = "сімсот";
        $this->prep_hang[8] = "вісімсот";
        $this->prep_hang[9] = "дев'ятьсот";

        $this->prep_namecurr[1] = "гривня"; // 1
        $this->prep_namecurr[2] = "гривні"; // 2, 3, 4
        $this->prep_namecurr[3] = "гривень"; // >4

        $this->prep_nametho[1] = "тисяча"; // 1
        $this->prep_nametho[2] = "тисячі"; // 2, 3, 4
        $this->prep_nametho[3] = "тисяч"; // >4

        $this->prep_namemil[1] = "мільйон"; // 1
        $this->prep_namemil[2] = "мільйона"; // 2, 3, 4
        $this->prep_namemil[3] = "мільйонів"; // >4

        $this->prep_namemrd[1] = "мільярд"; // 1
        $this->prep_namemrd[2] = "мільярда"; // 2, 3, 4
        $this->prep_namemrd[3] = "мільярдів"; // >4
    }
    public static function getTextByNum($number) {
        $obj = new self();
        return $obj->num2text_ua($number);
    }
}

