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

echo "##########################################<br>";
echo "GET ORDERLIST DATA<br>";
$qrord = "SELECT * FROM $ordtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = '$nopos'";
$objSQLord = new SQL($qrord);
$ord_datarow = $objSQLord->getResultOneRowArray();
echo "=====Orderlist data set : <br>";
printtable($ord_datarow, 'no');
$olissdt = $ord_datarow['datetimeissue_ol'];
echo "Orderlist issued date : $olissdt<br>";
$olissperiod = substr($olissdt, 2, 2) . substr($olissdt, 5, 2);
echo "OL Issued period : $olissperiod<br>";
echo "=====Generate predicted previous and next period :<br>";
$schperiod = $olissperiod;
echo "schperiod = $schperiod<br>";
$periodSet = get_beforeafterperiod($schperiod);
$prevschperiod = $periodSet['prevPeriod'];
$nextschperiod = $periodSet['nextPeriod'];
echo "prevschperiod = $prevschperiod<br>";
echo "nextschperiod = $nextschperiod<br>";
echo "=====Check Scheduling exists or not from currentperiod<br>";
$chkCurrSch = check_schRecordByPeriod($schperiod, $qno, $cid, $bid, $runno, $nopos);
$chkPrevSch = check_schRecordByPeriod($prevschperiod, $qno, $cid, $bid, $runno, $nopos);
$chkNextSch = check_schRecordByPeriod($nextschperiod, $qno, $cid, $bid, $runno, $nopos)
?>

<?php



function check_schRecordByPeriod($period, $qno, $cid, $bid, $runno, $nopos) {
    $schtab = "production_scheduling_$period";
    echo "===Checking $schtab for quono = $qno;cid = $cid;bid = $bid;runno = $runno;noposition = $nopos======<br>";
    $qrschcount = "SELECT COUNT(*) FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos AND status != 'cancelled'";
    $obJSQLschcount = new SQL($qrschcount);
    $schnumrow = $obJSQLschcount->getRowCount();
    display_codeblock($qrschcount);
    if ($schnumrow <= 0) {
        echo "Cannot find any record<br>";
        echo "===End $schtab for quono = $qno;cid = $cid;bid = $bid;runno = $runno;noposition = $nopos======<br>";
        return 'not exist';
    } else {
        echo "Found record!<br>";
        $qrsch = "SELECT * FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos AND status != 'cancelled'";
        $obJSQLsch = new SQL($qrsch);
        $schdatarow = $obJSQLsch->getResultOneRowArray();
        printtable($schdatarow, "no");
        $sch_sid = $schdatarow['sid'];
        echo "==++ Check Output Records ++==<br>";
        
        echo "==++ End Check Output Records ++==<br>";
        
        echo "===End $schtab for quono = $qno;cid = $cid;bid = $bid;runno = $runno;noposition = $nopos======<br>";
        return 'exist';
//        $qrsch = "SELECT * FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid =$bid AND runningno = '$runno' AND noposition $nopos ";
//        $objSQLsch = new SQL($qrsch);
//        $sch_dataset = $objSQLsch->getResultOneRowArray();
//        return $sch_dataset;
    }
}

function get_beforeafterperiod($period) {
    $ye = (int) substr($period, 0, 2);
    $mo = (int) substr($period, 2, 2);
    if (($mo + 1) > 12) {
        $nemo = 1;
        $neye = $ye + 1;
    } else {
        $nemo = $mo + 1;
        $neye = $ye;
    }
    if (($mo - 1) < 1) {
        $premo = 12;
        $preye = $ye - 1;
    } else {
        $premo = $mo - 1;
        $preye = $ye;
    }
    $prevPeriod = sprintf("%02d", $preye) . sprintf("%02d", $premo);
//    echo "prevPeriod = $prevPeriod<br>";
    $nextPeriod = sprintf("%02d", $neye) . sprintf("%02d", $nemo);
//    echo "nextPeriod = $nextPeriod<br>";
    return array('prevPeriod' => $prevPeriod, 'nextPeriod' => $nextPeriod);
}

function printtable($array, $multirow = 'no') {
    echo "<div style='width:1000px;max-width:1000px;overflow-x:auto'> ";
    if ($multirow == 'yes') {
        echo "<table>";
        echo "<tr>";
        foreach ($array[0] as $key => $val) {
            echo "<th>$key</th>";
        }
        echo "</tr border='1'>";
        foreach ($array as $array_row) {
            echo "<tr>";
            foreach ($array_row as $key => $val) {
                echo "<td>$val</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<table border='1' >";
        echo "<tr>";
        foreach ($array as $key => $val) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        foreach ($array as $key => $val) {
            echo "<td>$val</td>";
        }
        echo "</table>";
    }
    echo "</div><br>";
}

function display_codeblock($qr) {
    echo "<pre style='background-color:lightgray;height:50px;max-height:100px;max-width:700px;width:700px;overflow-x:auto'>$qr</pre>";
}
?>

