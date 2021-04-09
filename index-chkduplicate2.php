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

    $qrschcount = "SELECT COUNT(*) FROM $schtab ORDER BY sid ASC";
    $objSQLschcount = new SQL($qrschcount);
    $schnumrow = $objSQLschcount->getRowCount();
    if ($schnumrow <= 0) {
        $showSch = FALSE;
        $showSch_resp = "Cannot find record in $ordtab";
    } else {
        $showSch = TRUE;
        $limit = 1000;
        $totalpage = ceil($schnumrow / $limit);
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
        $qrsch = "SELECT * FROM $schtab ORDER BY sid ASC LIMIT $start, $limit";
        $objSQLsch = new SQL($qrsch);
        $schdataset = $objSQLsch->getResultRowArray();
        $sch_detaillist = array();
        foreach ($schdataset as $schdatarow) {
            $sch_sid = $schdatarow['sid'];
            $sch_quono = $schdatarow['quono'];
            $sch_com = $schdatarow['company'];
            $sch_jlfor = $schdatarow['jlfor'];
            $sch_rno = sprintf("%04d", $schdatarow['runningno']);
            $sch_npos = sprintf("%02d", $schdatarow['noposition']);
            $sch_cid = $schdatarow['cid'];
            $sch_bid = $schdatarow['bid'];
            $sch_cocode = substr($sch_quono, 0, 3);
            $sch_quoissdt = substr($sch_quono, 4, 4);
            $sch_dateissue = $schdatarow['date_issue'];
            $sch_completiondate = $schdatarow['completion_date'];
            $sch_status = $schdatarow['status'];
            $sch_jobcode = "$sch_jlfor $sch_cocode $sch_quoissdt $sch_rno $sch_npos";
//            echo "JOBCODE = $sch_jobcode<br><br>";
            $JCSIDdatarow = get_JCSIDRecord($sch_jobcode);
            if ($JCSIDdatarow == 'empty') {
                $jcsid_exist = 'no';
                $jcsid_remark = $jcsid_exist;
            } else {
                $jcsid_exist = 'yes';
                $jcsid_sid = $JCSIDdatarow['sid'];
                $jcsid_period = $JCSIDdatarow['period'];
                $jcsid_remark = $jcsid_exist . ", SID = $jcsid_sid on period = $jcsid_period ";
            }
            $periodSet = get_beforeafterperiod($period);
            $nextschPeriod = $periodSet['nextPeriod'];
            $nextsch_exist = check_schRecordByPeriod($nextschPeriod, $sch_quono, $sch_cid, $sch_bid, $sch_rno, $sch_npos);
            if ($nextsch_exist == 'exist') {
                $nextsch_remark = "Duplicate Record in $period and $nextschPeriod";
            } else {
                $nextsch_remark = "Not Duplicate";
            }

            $sch_detaillist[] = array(
                'jobcode' => $sch_jobcode,
                'sid' => $sch_sid,
                'qno' => $sch_quono,
                'com' => $sch_com,
                'jlfor' => $sch_jlfor,
                'date_issue' => $sch_dateissue,
                'completion_date' => $sch_completiondate,
                'rno' => $sch_rno,
                'npos' => $sch_npos,
                'cid' => $sch_cid,
                'bid' => $sch_bid,
                'jcsid_exist' => $jcsid_exist,
                'jcsid_remark' => $jcsid_remark,
                'nextPeriod' => $nextschPeriod,
                'nextsch_exist' => $nextsch_exist,
                'nextsch_remark' => $nextsch_remark,
                'status' => $sch_status
            );
        }
    }
}

function get_JCSIDRecord($jobcode) {
    $qr = "SELECT * FROM jobcodesid WHERE jobcode = '$jobcode'";
    $objSQL = new SQL($qr);
    $result = $objSQL->getResultOneRowArray();
    if (!empty($result)) {
        return $result;
    } else {
        return 'empty';
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
//    $qrschcount = "SELECT COUNT(*) FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos AND status != 'cancelled'";
    $qrschcount = "SELECT COUNT(*) FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos";
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

        <?php #include"navmenu.php";        ?>

        <div class="container-fluid">

            <div class="page-header" id="banner">
                <div class="row">
                    <div class="col-md">
                        <h1>CHECK DUPLICATE SCHEDULING DATAS</h1>
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
                if (isset($_POST['period'])) {
                    if ($showSch) {
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
                                if ($enditem > $schnumrow) {
                                    $enditem = $schnumrow;
                                }
                                $headertext = "Showing $startitem - $enditem of $schnumrow items";
                                ?>
                                <p class='page-header'>Data Set : <?php echo $headertext; ?> </p>
                                <table>
                                    <thead>
                                        <tr>
                                            <td class="bg-info">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            <td>Scheduling Duplicated</td>
                                        </tr>
                                        <tr>
                                            <td class="bg-danger">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                            <td>Cancelled Joblist</td>
                                        </tr>
                                    </thead>
                                </table>
                                <table class='table table-striped table-bordered table-responsive'>
                                    <thead>
                                        <tr>
                                            <th>Jobcode</th>
                                            <th>SID</th>
                                            <th>CID</th>
                                            <th>BID</th>
                                            <th>Date Issue</th>
                                            <th>Completion Date</th>
                                            <th>in Jobcodesid</th>
                                            <th>in <?php echo $nextschPeriod; ?></th>
                                            <!--<th>Datetime Quotation</th>-->
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($sch_detaillist as $sch_detailrow) {
//                                            if ($ordvsch_row['chkCount'] > 1) {
                                            if ($sch_detailrow['status'] == 'cancelled') {
                                                echo "<tr class='bg-danger'>";
                                            } elseif ($sch_detailrow['nextsch_exist'] == 'exist') {
                                                echo "<tr class='bg-info'>";
                                            } else {
                                                echo "<tr>";
                                            }
                                            ?>
                                        <td><?php echo $sch_detailrow['jobcode']; ?></td>
                                        <td><?php echo $sch_detailrow['sid']; ?></td>
                                        <td><?php echo $sch_detailrow['cid']; ?></td>
                                        <td><?php echo $sch_detailrow['bid']; ?></td>
                                        <td><?php echo $sch_detailrow['date_issue']; ?></td>
                                        <td><?php echo $sch_detailrow['completion_date']; ?></td>
                                        <td><?php echo $sch_detailrow['jcsid_remark']; ?></td>
                                        <td><?php echo $sch_detailrow['nextsch_remark']; ?></td>
                                        <td><?php echo $sch_detailrow['status']; ?></td>
                                        <?php
                                        if ($sch_detailrow['nextsch_exist'] == 'exist') {
                                            echo "
                                                    <td>
                                                        <a target='_blank' href = 'repairduplicatescheduling2.php?period=$period&sid={$sch_detailrow['sid']}&qno={$sch_detailrow['qno']}&com={$sch_detailrow['com']}&cid={$sch_detailrow['cid']}&bid={$sch_detailrow['bid']}&runno={$sch_detailrow['rno']}&nopos={$sch_detailrow['npos']}'
                                                            class='btn btn-warning btn-sm'>Repair Scheduling</a>
                                                    </td>";
                                        } else {
                                            echo "<td>&nbsp;</td>";
                                        }
                                        ?>
                                        <?php
                                        echo"</tr>";
//                                        }
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
                        echo "<h3>$showSch_resp</h3>";
                    }
                }
                ?>
            </div>

        </div>
        <?php include"footer.php" ?>
    </body>
</html>


