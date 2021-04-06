<?php

function convertPeriod($year, $mon) {

    $stryear = strval($year);
    $strmon = strval($mon);

    if (strlen($mon) == 1) {

        $mon = "0" . $mon;
    }

    $twoDigitYear = substr($year, 2);
    // echo "\$twoDigitYear = $twoDigitYear <br>";
    $period = $twoDigitYear . $mon;
    // echo "\$period = $period <br>";
    return $period;
}

Class DateNow {

    protected $datetimenow;
    protected $currentyear;
    protected $datdat;

    function __construct() {


        $dt = new DateTime();
        $now = $dt->format('Y-m-d H:i:s');
        $year = $dt->format('Y');
        $this->datetimenow = $now;

        $this->currentyear = $year;

        $datdat = date('ym');
        $this->datdat = $datdat;
    }

    Public function datetimenow() {

        $datetimenow = $this->datetimenow;

        return $datetimenow;
    }

    Public function currentYear() {



        $currentyear = $this->currentyear;

        return $currentyear;
    }

    Public function strPeriod() {

        $datdat = $this->datdat;

        return $datdat;
    }

    Public function intPeriod() {



        $intperiod = (int) $this->datdat;

        return $intperiod;
    }

}

Class Period {

    protected $ymd_date;
    protected $today_ymd_date;
    protected $seconds;
    protected $minutes;
    protected $hours;
    protected $mday;
    protected $wday;
    protected $mon;
    protected $year;
    protected $weekday;
    protected $month;
    protected $period;

    function __construct() {
        $date_array = getdate();
        //foreach($row as $key=>$value) { ${$key} = $value; }// assign all 
        foreach ($date_array as $key => $val) {
            //print "$key = $val<br />";
            ${$key} = $val;
        }
        // echo "=================================<br>";
        // echo "variables define define after Period object instanciated <br>";
        // echo "\$seconds = $seconds <br>";
        // echo "\$minutes = $minutes <br>";
        // echo "\$hours = $hours <br>";
        // echo "\$mday = $mday <br>";
        // echo "\$wday = $wday <br>";
        // echo "\$mon = $mon <br>";
        // echo "\$year = $year <br>";
        // echo "\$weekday = $weekday <br>";
        // echo "\$month = $month <br>";
        // echo "=================================<br>";
        // $formated_date .= $date_array['mday'] . "-";
        // $formated_date .= $date_array['mon'] . "-";
        // $formated_date .= $date_array['year'];
        $this->seconds = $seconds;
        $this->minutes = $minutes;
        $this->hours = $hours;
        $this->mday = $mday;
        $this->wday = $wday;
        $this->mon = $mon;
        $this->year = $year;
        $this->weekday = $weekday;
        $this->month = $month;

        // echo substr("$year",1)."<br>";
        //$period = 
    }

    Public function generateYears() {
        $currentYear = $this->year;
        $endYear = $this->year + 1;
        // echo "\$currentYear  = $currentYear  <br>";
        // echo "\$endYear  = $endYear  <br>";
        $var = intval($currentYear);
        // echo "\$var = $var <br>";
        $yearset = array();
        array_push($yearset, $currentYear, $endYear);

        // print_r($yearset);
        return $yearset;
    }

    public function getTodayDateYMD() {// get Year-Month-Day foramt of current date
        $formated_date = "";
        $mday = $this->mday;
        if (strlen($mday) == 1) {

            $mday = "0" . $mday;
        }
        $mon = $this->mon;
        if (strlen($mon) == 1) {

            $mon = "0" . $mon;
        }

        $year = $this->year;
        $formated_date .= $mday . "-";
        $formated_date .= $mon . "-";
        $formated_date .= $year;
        $formated_date = $year . "-" . $mon . "-" . $mday;
        // print $formated_date;
        // echo "<br>";
        return $formated_date;

        // }
    }

    public function getCurrentDateMonthYM() {
        $formated_date = "";
        $mday = $this->mday;
        if (strlen($mday) == 1) {

            $mday = "0" . $mday;
        }
        $mon = $this->mon;
        if (strlen($mon) == 1) {

            $mon = "0" . $mon;
        }

        $year = $this->year;
//            $formated_date .= $mday. "-";
        $formated_date .= $mon . "-";
        $formated_date .= $year;
        $formated_date = $year . "-" . $mon;
        // print $formated_date;
        // echo "<br>";
        return $formated_date;
    }

    public function getTodayDateDMY() {// get Day-Month-Year foramt of current date
        $mday = $this->mday;
        if (strlen($mday) == 1) {

            $mday = "0" . $mday;
        }
        $mon = $this->mon;
        if (strlen($mon) == 1) {

            $mon = "0" . $mon;
        }

        $year = $this->year;
        $formated_date = $mday . "-" . $mon . "-" . $year;
        // print $formated_date;
        // echo "<br>";
        return $formated_date;

        // }
    }

    public function getcurrentPeriod() {

        $year = $this->year;
        $mon = $this->mon;

        if (strlen($mon) == 1) {

            $mon = "0" . $mon;
        }
        $period = $this->period;
        $twoDigitYear = substr($year, 2);
        // echo "\$twoDigitYear = $twoDigitYear <br>";
        $period = $twoDigitYear . $mon;
        // echo "\$period = $period <br>";
        return $period;
    }

    public function getcurrentYear() {

        $year = $this->year;

        return $year;
    }

    public function getlastPeriod() {

        $year = $this->year;
        $mon = $this->mon;
        $year = intval($year);
        $mon = intval($mon);
        // echo "\$year = $year ,  \$mon = $mon   , line 125<br> ";
        if ($mon == 1) {
            $mon = 12;
            $year -= 1;
        } else {
            $mon = intval($mon - 1);
        }
        $year = strval($year);
        $mon = strval($mon);
        // echo "\$year = $year ,  \$mon = $mon   , line 134<br> ";
        if (strlen($mon) == 1) {

            $mon = "0" . $mon;
        }
        // $period = $this->period;

        $twoDigitYear = substr($year, 2);
        // echo "\$twoDigitYear = $twoDigitYear <br>";
        $lastperiod = $twoDigitYear . $mon;
        // echo "\$lastperiod = $lastperiod <br>";
        return $lastperiod;
    }

}

// Class DiffPeriod extends Period{
// 	protected $ymd_date;
//  	protected $today_ymd_date;
//  	protected $seconds;
//  	protected $minutes;
//  	protected $hours;
//  	protected $mday;
//  	protected $wday;
//  	protected $mon;
//  	protected $year;
//  	protected $weekday;
//  	protected $month;
//  	protected $period;
// 	function __construct ($dateYMD){
// 	}
// }
Class YearMonth extends Period {

    protected $ymd_date;
    protected $today_ymd_date;
    protected $seconds;
    protected $minutes;
    protected $hours;
    protected $mday;
    protected $wday;
    protected $mon;
    protected $year;
    protected $weekday;
    protected $month;
    protected $period;
    protected $currentYYYYmm;
    protected $EndYYYmm;

    function __construct($currentYYYYmm, $EndYYYYmm) {

        $this->currentYYYYmm = $currentYYYYmm;
        $this->EndYYYYmm = $EndYYYYmm;
    }

    public function getaSetOfYearMonth() {

        $CurrentYYYYmm = $this->currentYYYYmm;
        $EndYYYYmm = $this->EndYYYYmm;
        $mon = $this->mon;

//        echo "in function getaSetOfYearMonth() -> "
//        . "\$CurrentYYYYmm = $CurrentYYYYmm , \$EndYYYYmm = $EndYYYYmm <br>";

        $objYM = new Period();

        $CurrentYM = $objYM->getCurrentDateMonthYM();
//         echo "in function getaSetOfYearMonth() -> "
//        . "\$CurrentYM = $CurrentYM <br>";
        $year = $objYM->year;
        $mon = $objYM->mon;

//        substr(string,start,length)
        $endyear = intval(substr($EndYYYYmm, 0, 4));
        $startyear = intval($year);
        $startmonth = intval($mon);
        $endmonth = intval(substr($EndYYYYmm, 5, 2));
//        echo "\$startyear = $startyear , \$endyear = $endyear <br>";
//        echo "\$startmonth = $startmonth , \$endmonth = $endmonth <br>";
//        echo "\$year = $year , \$month = $mon <br>";
        $startPeriod = convertPeriod($startyear, $startmonth);
        $endPeriod = convertPeriod($endyear, $endmonth);
//        
//        echo "\$startPeriod = $startPeriod , \$endPeriod = $endPeriod <br>";

        $objPeriod = new generatePeriod($startPeriod, $endPeriod);
        $periodSet = $objPeriod->generatePeriod3();
//        var_dump($periodSet);
        $resultArray = array();
        foreach ($periodSet as $element) {
            //$arrayname[indexname] = $value;
            $stryear = "20" . strval(substr($element, 0, 2));
            $strmon = strval(substr($element, 2, 2));
            if (strlen($strmon) == 1) {

                $strmon = "0" . $strmon;
            }

            $yyyymm = $stryear . "-" . $strmon;

            //echo "$element  : $yyyymm<br>";
//            array_push($resultArray, '"$element"=>$yyyymm');
            $resultArray[$element] = $yyyymm;
        }
//        $resultArray = array();
//        for ($i = $startyear; $i >= $endyear; $i--){
//            echo "$i <br>";
//            array_push($resultArray, $i);
//            for($j = $startmonth; $j >= $endmonth; $j--){
//                echo "$j <br>";
//            }
//        }
//        for($i = $CurrentYYYYmm; $i >= $EndYYYYmm; $i--){
//            
//            echo "$i";
//        }

        return $resultArray;
    }

}

Class DiffPeriod extends Period {

    protected $ymd_date;
    protected $today_ymd_date;
    protected $seconds;
    protected $minutes;
    protected $hours;
    protected $mday;
    protected $wday;
    protected $mon;
    protected $year;
    protected $weekday;
    protected $month;
    protected $period;

    function __construct($dateYMD) {
        
    }

}

Class generatePeriod extends Period {

    protected $ymd_date;
    protected $today_ymd_date;
    protected $seconds;
    protected $minutes;
    protected $hours;
    protected $mday;
    protected $wday;
    protected $mon;
    protected $year;
    protected $weekday;
    protected $month;
    protected $currentPeriod;
    protected $endPeriod;

    function __construct($currentPeriod, $endPeriod) {

        $this->currentPeriod = $currentPeriod;
        $this->endPeriod = $endPeriod;
    }

    function generatePeriod() {
        $currentPeriod = $this->currentPeriod;
        $endPeriod = $this->endPeriod;
        // echo "\$currentperiod  = $currentperiod  <br>";
        $var = intval($currentPeriod);
        // echo "\$var = $var <br>";
        $periodset = array();
        $j = 0;
        for ($i = $var; $i >= $endPeriod; $i--) {
            // echo "\$i = $i  ,  \$j = $j <br>";
            if ($j != 12) {
                array_push($periodset, $i);
                $j++;
            } else {
                $i = $i - 87;
                $j = 0;
            }
        }
        return $periodset;
        // var_dump($periodset;);
    }

    function generatePeriod2() {

        $currentPeriod = $this->currentPeriod;
        $endPeriod = $this->endPeriod;
        // echo "\$currentperiod  = $currentperiod  <br>";
        $var = intval($currentPeriod);
        // echo "\$var = $var <br>";
        $currentMonth = substr($currentPeriod, -2, 2);
        // echo "\$currentMonth = $currentMonth <br>";
        $endmonth = 01;
        $currentYear = substr($currentPeriod, 0, 2);
        // echo "\$currentYear  = $currentYear  <br>";
        $endYear = substr($endPeriod, 0, 2);
        // echo "\$endYear  = $endYear <br>";

        $periodset = array();
        for ($yr = $currentYear; $yr >= $endYear; $yr--) {

            // echo "\$yr = $yr <br>";
            if ($yr == $currentYear) {

                for ($month = $currentMonth; $month <= $endmonth; $month++) {
                    if (strlen($month) == 1) {

                        $month = "0" . $month;
                    }
                    // echo "\$month = $month <br>";
                    $per = $yr . $month;
                    // echo "$per <br>";
                    array_push($periodset, $per);
                }
            } else {
                for ($month = 12; $month >= 1; $month--) {
                    if (strlen($month) == 1) {

                        $month = "0" . $month;
                    }
                    // echo "\$month = $month <br>";
                    $per = $yr . $month;
                    // echo "$per <br>";
                    array_push($periodset, $per);
                }
            }
        }
        return $periodset;
    }

    function generatePeriod3() {

        $currentPeriod = $this->currentPeriod;
        $endPeriod = $this->endPeriod;
        // echo "\$currentPeriod  = $currentPeriod  <br>";
        // echo "\$endPeriod  = $endPeriod  <br>";
        $varCurrent = intval($currentPeriod);
        // echo "\$varCurrent = $varCurrent <br>";
        $varEnd = intval($endPeriod);
        // echo "\$varEnd = $varEnd <br>";
        $currentMonth = substr($currentPeriod, -2, 2);
        // echo "\$currentMonth = $currentMonth <br>";
        $endmonth = substr($endPeriod, -2, 2);
        // echo "\$endmonth = $endmonth <br>";
        $currentYear = substr($currentPeriod, 0, 2);
        // echo "\$currentYear  = $currentYear  <br>";
        $endYear = substr($endPeriod, 0, 2);
        // echo "\$endYear  = $endYear <br>";

        $periodset = array();

        for ($p = $varCurrent; $p >= $varEnd; $p--) {
            # code...

            if (strval(substr($p, -2, 2)) == '00') {
                // echo "do not print $p <br>";
                # code...
            } elseif (substr($p, -2, 2) < 13) {
                // echo "$p <br>";
                array_push($periodset, strval($p));
            }
        }



        // for($yr = $currentYear; $yr >= $endYear; $yr-- ){
        // 	echo "\$yr = $yr <br>";
        // 	if ($yr == $currentYear) {
        // 		for($month = $currentMonth; $month <= $endmonth; $month++){
        // 				if (strlen($month) == 1 ) {
        // 				$month = "0".$month;
        // 			}
        // 			echo "\$month = $month <br>";
        // 			$per = $yr.$month;
        // 			echo "$per <br>";
        // 			array_push($periodset, $per);
        // 		}
        // 	}else{
        // 		for($month = 12; $month >= 1 ; $month--){
        // 				if (strlen($month) == 1 ) {
        // 				$month = "0".$month;
        // 			} 					
        // 			echo "\$month = $month <br>";
        // 			$per = $yr.$month;
        // 			// echo "$per <br>";
        // 			array_push($periodset, $per);					
        // 		}
        // 	}
        // }
        return $periodset;
    }

}

Class NextPeriod extends Period {

    protected $ymd_date;
    protected $today_ymd_date;
    protected $seconds;
    protected $minutes;
    protected $hours;
    protected $mday;
    protected $wday;
    protected $mon;
    protected $year;
    protected $weekday;
    protected $month;
    protected $currentPeriod;

    function __construct($currentPeriod) {
        $this->currentPeriod = $currentPeriod;
    }

    public function get_nextPeriod() {
        $currentPeriod = $this->currentPeriod;
        $YY = (int) substr($currentPeriod, 0, 2);
        $MM = (int) substr($currentPeriod, 2, 2);

        if ($MM + 1 > 12) {
            $nMM = sprintf('%02d', 1);
            $nYY = sprintf('%02d', $YY + 1);
        } else {
            $nMM = sprintf('%02d', $MM + 1);
            $nYY = sprintf('%02d', $YY);
        }

        $nextPeriod = $nYY . $nMM;
        return $nextPeriod;
    }

}

Class DateDiff {

    protected $yourDate;
    protected $ChangeDate;

    function __construct($yourDate, $ChangeDate) {

        $this->yourDate = $yourDate;
        $this->ChangeDate = $ChangeDate;
    }

    function DateDiffCompare() {

        $yourDate = $this->yourDate;
        $ChangeDate = $this->ChangeDate;
        // echo "\$yourDate = $yourDate ,  \$ChangeDate = $ChangeDate <br>";
        $yourDateObj = date_create($yourDate);
        // echo "\$yourDateObj = $yourDateObj <br>";
        // echo date_format($yourDateObj, 'Y-m-d');
        // echo "<br>";


        $yourDateValue = strtotime($yourDate);
        $ChangeDateValue = strtotime($ChangeDate);

        $datediff = $yourDateValue - $ChangeDateValue;
        // echo "\$datediff = $datediff <br>";
        if ($datediff >= 0) {
            $IsyourDateAfterChangeDate = 'yes';
        } elseif ($datediff < 0) {
            $IsyourDateAfterChangeDate = 'no';
        }
        //echo "The variable \$IsyourDateAfterChangeDate = $IsyourDateAfterChangeDate <br> ";
        return $IsyourDateAfterChangeDate;
    }

}

Class FivePlyClassDate {

    function __construct() {
        
    }

    public function getFivePlyDateYMD() {// get Year-Month-Day foramt of current date
        $mday = '19';
        $mon = '07';
        $year = '2019';
        $formated_date .= $mday . "-";
        $formated_date .= $mon . "-";
        $formated_date .= $year;
        $formated_date = $year . "-" . $mon . "-" . $mday;
        // print $formated_date;
        // echo "<br>";
        return $formated_date;

        // }
    }

}

Class DateDayMonthYear {

    private $date;

    function __construct($date = null) {
        if ($date != null) {
            $objdate = date_create($date);
        } else {
            $objdate = date_create(date('Y-m-d'));
        }
        #echo 'date = '.$date.'\n';
        $this->date = $objdate;
    }

    public function get_day() {
        $date = $this->date;
        $day = date_format($date, 'l');
        return $day;
    }

    public function get_month() {
        $date = $this->date;
        $month = date_format($date, 'F');
        return $month;
    }

    public function get_total_day() {
        $date = $this->date;
        $totalday = date_format($date, 't');
        return $totalday;
    }

}

Class GenerateDateArray {

    protected $dt;
    protected $yearNow;
    protected $startYear;

    function __construct() {
        $dt = new DateTime();
        $this->dt = $dt;
        $this->yearNow = date_format($dt, 'Y');
        $this->startYear = 2001;
    }

    public function generateYearArray() {
        $startYear = $this->startYear;
        $yearNow = $this->yearNow;
        $yearArr = array();
        for ($year = $yearNow; $year >= $startYear; $year--) {
            $yearArr[] = $year;
        }
        return $yearArr;
    }

    public function generateMonthArray($year) {
        $dt = $this->dt;
        $yearNow = $this->yearNow;
        $monthNow = date_format($dt, 'm');
        $monthArr = array();
        if ($year == $yearNow) {
            for ($month = $monthNow; $month >= 1; $month--) {
                $monthArr[] = sprintf('%02d',$month);
            }
        } else {
            for ($month = 12; $month >= 1; $month--) {
                $monthArr[] = sprintf('%02d',$month);
            }
        }
        return $monthArr;
    }

    public function generateDayArray($year, $month) {
        $dt = $this->dt;
        $yearNow = $this->yearNow;
        $monthNow = date_format($dt, 'm');
        $dayNow = date_format($dt, 'd');
        $dayArr = array();
        if ($year == $yearNow && $month == $monthNow) {
            for ($day = 1; $day <= $dayNow; $day++) {
                $dayArr[] = $day;
            }
        } else {
            $date = date_create("$year-$month-01");
            #echo "date = $date";
            $totalDate = date_format($date, 't');
            for ($day = 1; $day <= $totalDate; $day++) {
                $dayArr[] = $day;
            }
        }
        return $dayArr;
    }

}
