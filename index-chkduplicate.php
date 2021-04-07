<?php
include_once 'class/dbh.inc.php';
include_once 'class/variables.inc.php';
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

$periodList = getPeriodList();

if (isset($_POST['period'])) {
    $period = $_POST['period'];
    $com = $_POST['com'];
    $com_sml = strtolower($com);
    $com_cap = strtoupper($com);
    $ordtab = "orderlist_{$com_sml}_$period";
    $schtab = "production_scheduling_$period";

    $qrordcount = "SELECT COUNT(*) FROM $ordtab ORDER BY oid ASC";
    $objSQLordcount = new SQL($qrordcount);
    $ordnumrow = $objSQLordcount->getRowCount();
    if ($ordnumrow <= 0) {
        $showOrd = FALSE;
        $showOrd_resp = "Cannot find record in $ordtab";
    } else {
        $showOrd = TRUE;
        $limit = 1000;
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
        $qrord = "SELECT * FROM $ordtab ORDER BY oid ASC LIMIT $start, $limit";
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
            $quoissdt = $ord_datarow['datetimeissue_quo'];
            $ordissdt = $ord_datarow['datetimeissue_ol'];
            $ordiss_period = substr($ordissdt, 2, 2) . substr($ordissdt, 5, 2);
            $ba_period = get_beforeafterperiod($ordiss_period);
            $ord_prevperiod = $ba_period['prevPeriod'];
            $ord_nextperiod = $ba_period['nextPeriod'];
            $remark1 = "Period1=$ordiss_period<br>Period2=$ord_prevperiod<br>Period3=$ord_nextperiod";
            if ($jlissue != 'issued') {
                $jli_stat = 'issued';
            } else {
                $jli_stat = 'no';
            }
            $chkCount = 0;
            $chkPeriod = $ordiss_period; //init
            $chkSchExistCurr = check_schRecordByPeriod($ordiss_period, $qno, $cid, $bid, $runno, $nopos);
            if ($chkSchExistCurr == 'exist') {
                $chkPeriod = $ordiss_period;
                $chkCount++;
            }
            $chkSchExistPrev = check_schRecordByPeriod($ord_prevperiod, $qno, $cid, $bid, $runno, $nopos);
            if ($chkSchExistPrev == 'exist') {
                $chkPeriod = $ord_prevperiod;
                $chkCount++;
            }
            $chkSchExistNext = check_schRecordByPeriod($ord_nextperiod, $qno, $cid, $bid, $runno, $nopos);
            if ($chkSchExistNext == 'exist') {
                $chkPeriod = $ord_nextperiod;
                $chkCount++;
            }

            if ($chkCount > 1) {
                $remark2 = "Found duplicate in $chkCount table(s)";
            } elseif ($chkCount == 1) {
                $remark2 = "Record found in production_scheduling_$chkPeriod";
                if ($chkPeriod == $ordiss_period) {
                    $remark2 .= "<br>The same period as orderlist ($ordiss_period)";
                } else {
                    $remark2 .= "<br>Different period thanorderlist ($ordiss_period)";
                }
            } else {
                $remark2 = "Cannot find scheduling data.";
            }

            $ordvsch[] = array(
                'qno' => $qno,
                'com' => $com_sml,
                'cid' => $cid,
                'bid' => $bid,
                'runno' => $runno,
                'noposition' => $nopos,
                'jli_stat' => $jli_stat,
//                'quoissdt' => $quoissdt,
                'ordissdt' => $ordissdt,
                'ordissperiod' => $ordiss_period,
                'remark1' => $remark1,
                'period1' => $chkSchExistCurr,
                'period2' => $chkSchExistPrev,
                'period3' => $chkSchExistNext,
                'remark2' => $remark2,
                'chkCount' => $chkCount,
                'schperiod' => $chkPeriod
            );
            unset($chkPeriod);
        }
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

function check_schRecordByPeriod($period, $qno, $cid, $bid, $runno, $nopos) {
    $schtab = "production_scheduling_$period";
    $qrschcount = "SELECT COUNT(*) FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos AND status != 'cancelled'";
    $obJSQLschcount = new SQL($qrschcount);
    $schnumrow = $obJSQLschcount->getRowCount();
    if ($schnumrow <= 0) {
        return 'not exist';
    } else {
        return 'exist';
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

        <?php #include"navmenu.php";      ?>

        <div class="container-fluid">

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
                                        <button type="submit" class="btn btn-info" id='page' name='page' value='<?php echo 1; ?>'>< First</button>
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
                        </form>
                        <div class='row'>
                            <div class='col-md'>
                                <?php
                                $startitem = $start + 1;
                                $enditem = $start + $limit;
                                if ($enditem > $ordnumrow){
                                    $enditem = $ordnumrow;
                                }
                                $headertext = "Showing $startitem - $enditem of $ordnumrow items";
                                ?>
                                <p class='page-header'>Data Set : <?php echo $headertext; ?> </p>
                                <table>
                                    <thead>
                                        <tr>
                                            <td class="bg-warning">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            <td>Scheduling Period is different from Orderlist Period</td>
                                        </tr>
                                        <tr>
                                            <td class="bg-danger">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            <td>Scheduling duplicate</td>
                                        </tr>
                                        <tr>
                                            <td class="bg-primary">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            <td>Scheduling doesn't exists</td>
                                        </tr>
                                    </thead>
                                </table>
                                <table class='table table-striped table-bordered table-responsive'>
                                    <thead>
                                        <tr>
                                            <th>Quono</th>
                                            <th>Com</th>
                                            <th>CID</th>
                                            <th>BID</th>
                                            <th>Run No</th>
                                            <th>No. Pos</th>
                                            <th>Joblist issued?</th>
                                            <!--<th>Datetime Quotation</th>-->
                                            <th>OL Date Issue</th>
                                            <th>Detect Period</th>
                                            <th>In Period1</th>
                                            <th>In Period2</th>
                                            <th>In Period3</th>
                                            <th>Remark</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($ordvsch as $ordvsch_row) {
                                            if ($ordvsch_row['chkCount'] < 1) {
                                                echo "<tr class='bg-primary'>";
                                            } elseif ($ordvsch_row['chkCount'] > 1) {
                                                echo "<tr class='bg-danger'>";
                                            } elseif ($ordvsch_row['chkCount'] == 1) {
                                                if (strpos($ordvsch_row['remark2'], 'Different period') !== FALSE) {
                                                    echo "<tr class='bg-warning'>";
                                                } else {
                                                    echo "<tr>";
                                                }
                                            }
                                            ?>
                                        <td><?php echo $ordvsch_row['qno']; ?></td>
                                        <td><?php echo $ordvsch_row['com']; ?></td>
                                        <td><?php echo $ordvsch_row['cid']; ?></td>
                                        <td><?php echo $ordvsch_row['bid']; ?></td>
                                        <td><?php echo $ordvsch_row['runno']; ?></td>
                                        <td><?php echo $ordvsch_row['noposition']; ?></td>
                                        <td><?php echo $ordvsch_row['jli_stat']; ?></td>
                                        <!--<td>Datetime Quotation</td>-->
                                        <td><?php echo $ordvsch_row['ordissdt']; ?></td>
                                        <td><?php echo $ordvsch_row['remark1']; ?></td>
                                        <td><?php echo $ordvsch_row['period1']; ?></td>
                                        <td><?php echo $ordvsch_row['period2']; ?></td>
                                        <td><?php echo $ordvsch_row['period3']; ?></td>
                                        <td><?php echo $ordvsch_row['remark2']; ?></td>
                                        <?php
                                        if ($ordvsch_row['chkCount'] > 1) {
                                            echo "
                                                    <td>
                                                        <a target='_blank' href = 'repairduplicatescheduling.php?period=$period&qno={$ordvsch_row['qno']}&com={$ordvsch_row['com']}&cid={$ordvsch_row['cid']}&bid={$ordvsch_row['bid']}&runno={$ordvsch_row['runno']}&nopos={$ordvsch_row['noposition']}'
                                                            class='btn btn-warning btn-sm'>Repair Scheduling</a>
                                                    </td>";
                                        } else {
                                            echo "<td>&nbsp;</td>";
                                        }
                                        ?>
                                        <?php
                                        echo"</tr>";
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <form action='' target='_parent' method="POST">
                            <input type="hidden" id='period' name='period' value="<?php echo $period; ?>" />
                            <input type="hidden" id='com' name='com' value="<?php echo $com_sml; ?>" />
                            <div class='row'>
                                <div class="col-md-6">
                                    <div class="btn-group" role="group" aria-label="Pagination">
                                        <button type="submit" class="btn btn-info" id='page' name='page' value='<?php echo 1; ?>'>< First</button>
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


