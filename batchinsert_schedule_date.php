<?php
include_once 'class/dbh.inc.php';
include_once 'class/variables.inc.php';
include_once 'class/legacy_dbh.inc.php';
include_once 'class/legacy_variables.inc.php';
include_once 'class/phhdate.inc.php';

//to check debug $_POST

function getPeriodList() {
    $objDate = new DateNow();
    $currentPeriod_int = $objDate->intPeriod();
    $currentPeriod_str = $objDate->strPeriod();

    $EndYYYYmm = 2001;
    $objPeriod = new generatePeriod($currentPeriod_int, $EndYYYYmm);
    $setofPeriod = $objPeriod->generatePeriod3();
    return $setofPeriod;
}

function createSchedulingData($schtab, $ord_dataset) {

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
    echo " \$completion_date = $completion_date<br>";
    $detectStr = substr($completion_date, 2, 1);
    if ($detectStr == '-') {
        # code...
        $date = "";
        $date = date_create_from_format('d-m-y', $completion_date);
        $newformat = date_format($date, 'Y-m-d');
        echo "\$newformat = $newformat <br>";
        echo "<br>";
        $completion_date = $newformat;
        echo "\$completion_date = $completion_date <br>";
    }

    $Insert_Array = array();
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
    echo "===DEBUG LOG QR = $qrins_debug <br>";

    $objSQLlog = new SQLBINDPARAM($qrins, $Insert_Array);
    $insResult = $objSQLlog->InsertData2();
    echo "+++===LOG RESULT (Target : Localhost) = $insResult<br>";

    echo "<br>--------------------------------------------------------------------------------------------------<br>";

    $objLegacySQLlog = new legacySQLBINDPARAM($qrins, $Insert_Array);
    $insLegacyResult = $objLegacySQLlog->InsertData2();
    echo "===DEBUG LOG QR = $qrins_debug <br>";
    echo "+++===LEGACY LOG RESULT (Target : 192.168.102.11) = $insLegacyResult<br>";

    echo "<br>##########################################################################################<br>";

    return $insResult;
}

$periodList = getPeriodList();

if (isset($_POST['period'])) {
    $period = $_POST['period'];
    $com = $_POST['com'];
    $com_sml = strtolower($com);
    $com_cap = strtoupper($com);
    $ordtab = "orderlist_{$com_sml}_$period";
    $schtab = "production_scheduling_$period";

    $qrordcount = "SELECT COUNT(*) FROM $ordtab WHERE cid != 20506  AND cid != 507 AND jlissue = 'issued'   ORDER BY oid ASC";
    $objSQLordcount = new SQL($qrordcount);
    $ordnumrow = $objSQLordcount->getRowCount();
    if ($ordnumrow <= 0) {
        $showOrd = FALSE;
        $showOrd_resp = "Cannot find record in $ordtab";
    } else {
        $showOrd = TRUE;
        $limit = 100;
        $totalpage = ceil($ordnumrow / $limit);
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
        } else {
            $page = 1;
        }
        if ($page < 1) {
            $page = 1;
        }
        if ($page > $totalpage) {
            $page = $totalpage;
        }
        $start = ($page - 1) * $limit;
        $qrord = "SELECT * FROM $ordtab WHERE cid != 20506  AND cid != 507 AND jlissue = 'issued'   ORDER BY oid ASC LIMIT $start, $limit";
        $objSQLord = new SQL($qrord);
        $ord_dataset = $objSQLord->getResultRowArray();
        $ordvsch = array();
        foreach ($ord_dataset as $ord_datarow) {
            $qno = $ord_datarow['quono'];
            $cid = $ord_datarow['cid'];
            $bid = $ord_datarow['bid'];
            $runno = $ord_datarow['runningno'];
            $nopos = $ord_datarow['noposition'];
            $jlissue = $ord_datarow['jlissue'];
            if ($jlissue == 'issued') {
                $jli_stat = 'issued';
            } else {
                $jli_stat = 'no';
            }
            $chk_schRecord = check_schRecord($schtab, $qno, $cid, $bid, $runno, $nopos);
            if ($chk_schRecord == 'ok') {
                $sch_stat = 'exists!';
            } else {
                $sch_stat = 'no';
            }
            $ordvsch[] = array(
                'qno' => $qno,
                'com' => $com_sml,
                'cid' => $cid,
                'bid' => $bid,
                'runno' => $runno,
                'noposition' => $nopos,
                'jli_stat' => $jli_stat,
                'sch_stat' => $sch_stat
            );
        }
    }
}

function check_schRecord($schtab, $qno, $cid, $bid, $runno, $nopos) {
    $qrschcount = "SELECT COUNT(*) FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos ";
    $obJSQLschcount = new SQL($qrschcount);
    $schnumrow = $obJSQLschcount->getRowCount();
    if ($schnumrow <= 0) {
        return 'fail';
    } else {
        return 'ok';
//        $qrsch = "SELECT * FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid =$bid AND runningno = '$runno' AND noposition $nopos ";
//        $objSQLsch = new SQL($qrsch);
//        $sch_dataset = $objSQLsch->getResultOneRowArray();
//        return $sch_dataset;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include "header.php"; ?>

    <body>

<?php #include"navmenu.php";     ?>

        <div class="container">

            <div class="page-header" id="banner">
                <div class="row">
                    <div class="col-md">
                        <h1>CHECK ORDERLIST TO SCHEDULING DATAS</h1>
                    </div>
                </div>
                <br>
                <br>
                <div id="mainArea">
                    <form id='periodform' action='' target='_parent' method="POST">
                        <div class="row">
                            <div class="col-md-3">
                                <label class="control-label">Period</label>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label">&nbsp;</label>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <select id="period" name="period" class="custom-select">
                                    <?php
                                    foreach ($periodList as $data) {
                                        echo "<option value='$data'>$data</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="com" name="com" class="custom-select">
                                    <option value='pst'>PST</option>
                                    <option value='psvpmb'>PSVPMB</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-info btn-block">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php
                if ($_POST['period']) {
                    if ($showOrd) {
                        echo "<h3>Period =$period</h3>";
                        ?>
                        <form action='' target='_parent' method="POST">
                            <input type="hidden" id='period' name='period' value="<?php echo $period; ?>" />
                            <input type="hidden" id='com' name='com' value="<?php echo $com_sml; ?>" />
                            <div class='row'>
                                <div class="col-md-6">
                                    <div class="btn-group" role="group" aria-label="Pagination">
                                        <button type="submit" class="btn btn-primary" id='page' name='page' value='<?php echo 1; ?>'>< First</button>
                                        <button type="submit" class="btn btn-primary" id='page' name='page' value='<?php echo $page - 1; ?>'><< Prev</button>
                                        <button type="button" class="btn btn-primary disabled" disabled>
                                            <?php
                                            echo "page $page of $totalpage<br>";
                                            ?>
                                        </button>
                                        <button type="submit" class="btn btn-primary" id='page' name='page' value='<?php echo $page + 1; ?>'>Next ></button>
                                        <button type="submit" class="btn btn-info" id='page' name='page' value='<?php echo $totalpage; ?>'>Last >></button>
                                    </div>
                                </div>
                            </div>
                            <div class='row'>
                                <div class='col-md'>
                                    <p class='page-header'>Data Set :</p>
                                    <!-- <table class='table table-bordered table-responsive'>
                                        <thead>
                                            <tr>
                                                <th>Quono</th>
                                                <th>Company</th>
                                                <th>CID</th>
                                                <th>BID</th>
                                                <th>Running No</th>
                                                <th>No. Position</th>
                                                <th>Joblist issued?</th>
                                                <th>Scheduling Exist?</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody> -->
                                    <?php
                                    foreach ($ordvsch as $ordvsch_row) {
                                        // echo "<tr>";
                                        foreach ($ordvsch_row as $index => $val) {

                                            // echo "<td>$val</td>";
                                        }
                                        if ($ordvsch_row['sch_stat'] == 'no') {
//                                            echo "
////                                                    <td>
////                                                        <a target='_blank' href = 'createschedulingrecord.php?period=$period&qno={$ordvsch_row['qno']}&com={$ordvsch_row['com']}&cid={$ordvsch_row['cid']}&bid={$ordvsch_row['bid']}&runno={$ordvsch_row['runno']}&nopos={$ordvsch_row['noposition']}'
////                                                            class='btn btn-warning btn-sm disabled'>Generate Scheduling Data</a>
////                                                    </td>";

                                            $qno = $ordvsch_row['qno'];
                                            $bid = $ordvsch_row['bid'];
                                            $cid = $ordvsch_row['cid'];
                                            $runno = $ordvsch_row['runno'];
                                            $nopos = $ordvsch_row['noposition'];

                                            $ordtab = "orderlist_pst_$period";
                                            $schtab = "production_scheduling_$period";

                                            echo "<strong>GENERATING SCHEDULING RECORD FOR $qno</strong><br>";
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
                                                //$createResult = "The Create scheduling data is run here ";
                                                echo "<strong>Result : $createResult</strong><br>";
                                            }
                                        } else {
                                            // echo "<td>&nbsp;</td>";
                                        }
                                        ?>
                                        <?php
                                        // echo"</tr>";
                                    }
                                    ?>
                                    </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                        <?php
                    } else {
                        echo "<h3>$showOrd_resp</h3>";
                    }
                }
                ?>
            </div>

        </div>
        <?php include"footer.php" ?>
    </body>
</html>


