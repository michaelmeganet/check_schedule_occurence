<?php

include_once 'class/dbh.inc.php';
include_once 'class/variables.inc.php';
include_once 'class/phhdate.inc.php';

if (isset($_GET['period'])) {
    $period = $_GET['period'];
} else {
    die('Cannot reach page this way');
}
if (isset($_GET['qno'])) {
    $qno = $_GET['qno'];
} else {
    die('Cannot reach page this way');
}
if (isset($_GET['cid'])) {
    $cid = $_GET['cid'];
} else {
    die('Cannot reach page this way');
}
if (isset($_GET['bid'])) {
    $bid = $_GET['bid'];
} else {
    die('Cannot reach page this way');
}
if (isset($_GET['runno'])) {
    $runno = $_GET['runno'];
} else {
    die('Cannot reach page this way');
}
if (isset($_GET['nopos'])) {
    $nopos = $_GET['nopos'];
} else {
    die('Cannot reach page this way');
}

$ordtab = "orderlist_pst_$period";
$schtab = "production_scheduling_$period";

echo "<h3>GENERATING SCHEDULING RECORD FOR $qno</h3><br>";
echo "==== Fetch Orderlist record ====<br>";
$qrord = "SELECT * FROM $ordtab WHERE quono = '$qno' AND bid = $bid AND cid = $cid AND runningno = '$runno' AND noposition = '$nopos'";
$objSQLord = new SQL($qrord);
echo "\$qrord = $qrord<br";
$ord_dataset = $objSQLord->getResultOneRowArray();
if (empty($ord_dataset)) {
    echo "Failed to fetch data for quono =$qno; cid = $cid; bid=$bid; runningno = $runno; noposition= $nopos";
    exit();
} else {
    print_r($ord_dataset);
    echo "<br><br>";
    $createResult = createSchedulingData($schtab, $ord_dataset);
    echo "<h3>Result : $createResult</h3><br>";
    
}
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<?php

function createSchedulingData($schtab,$ord_dataset) {
    
        foreach ($ord_dataset as $key => $value) {
            ${$key} = $value;
            // echo "$key : $value \n"."<br>";
        }
        if ($bid == 1) {
            $jlfor = 'CJ';
        } elseif ($bid == 2) {
            $jlfor = 'SB';
        } else {
            $jlfor = '';
        }
        //echo " \$completion_date = $completion_date<br>";
        $date = date_create_from_format('d-m-y', $completion_date);
        $newformat = date_format($date, "Y-m-d");
        //echo "\$newformat = $newformat <br>";
        //echo "<br>";
        $completion_date = $newformat;
        // echo "\$completion_date = $completion_date <br>";

        $Insert_Array = array(
            'bid' => $bid,
            'qid' => $qid,
            'quono' => $quono,
            'company' => $company,
            'status' => $cusstatus,
            'cid' => $cid,
            'noposition' => $noposition,
            'quantity' => $quantity,
            'grade' => $grade,
            'mdt' => $mdt,
            'mdw' => $mdw,
            'mdl' => $mdl,
            'fdt' => $fdt,
            'fdw' => $fdw,
            'fdl' => $fdl,
            'process' => $process,
            'cncmach' => $cncmach,
            'aid_cus' => $aid_cus,
            'date_issue' => $date_issue,
            'source' => $source,
            'cuttingtype' => $cuttingtype,
            'custoolcode' => $custoolcode,
            'completion_date' => $completion_date,
            'runningno' => $runningno,
            'jlfor' => $jlfor,
            'jobno' => $jobno,
            'ivdate' => $ivdate,
            'operation' => $operation
        );

        $qrins = "INSERT INTO $schtab SET ";
        $qrins_debug = "INSERT INTO $schtab SET ";
        $arrCnt = count($Insert_Array);
        $cnt = 0;
        foreach ($Insert_Array as $key => $val) {
            $cnt++;
            $qrins .= " $key =:$key ";
            $qrins_debug .= " $key = '$val' ";
            if ($cnt != $arrCnt) {
                $qrins .= " , ";
                $qrins_debug .= " , ";
            }
        }

//            echo "<br><br>\$qrins = $qrins <br><br>";
//            echo "<br><br>\$qrins_debug= $$qrins_debug <br><br>";
            echo "<br>$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$<br>";

        $objSQLlog = new SQLBINDPARAM($qrins, $Insert_Array);
        $insResult = $objSQLlog->InsertData2();
            echo "===DEBUG LOG QR = $qrins_debug <br>";
            echo "+++===LOG RESULT = $insResult<br>";
    
    return $insResult;

    echo "<br>##########################################################################################<br>";
}
?>

