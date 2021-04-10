<?php
session_start();
include_once 'class/dbh.inc.php';
include_once 'class/variables.inc.php';
include_once 'class/phhdate.inc.php';

if (isset($_SESSION['duplicateSchList'])) {
    $duplicateSchList = $_SESSION['duplicateSchList'];
    unset($_SESSION['duplicateSchList']);
} else {
    die('Session file not detected.');
}

if (isset($_GET['period'])) {
    $period = $_GET['period'];
} else {
    die('Cannot reach page this way');
}
?>
<html>
    <head>

        <link rel="stylesheet" href="./docs/4/darkly/bootstrap.css" media="screen">
    </head>
    <body>
        <div class="container">
            <?php
            $totaldata = count($duplicateSchList);
            echo "Begin processing $totaldata datas.<br>";
            $repairSuccessCnt = 0;
            $repairFailCnt = 0;
            $repairFailArray = 0;
            foreach ($duplicateSchList as $duplicateSchDataRow) {
                $sid = $duplicateSchDataRow['sid'];
                $qno = $duplicateSchDataRow['qno'];
                $cid = $duplicateSchDataRow['cid'];
                $bid = $duplicateSchDataRow['bid'];
                $runno = $duplicateSchDataRow['rno'];
                $nopos = $duplicateSchDataRow['npos'];
                $jobcode = $duplicateSchDataRow['jobcode'];
                echo "=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+=+<br>";
                echo "PROCESSING JOBCODE = $jobcode<br>";

                $repairResult = repairDuplicateRecords($period, $sid, $qno, $cid, $bid, $runno, $nopos);
                $repairStatus = $repairResult['status'];
                if ($repairStatus == 'success') {
                    $repairSuccessCnt++;
                } else {
                    $repairFailCnt++;
                    $repairMsg = $repairResult['msg'];
                    $repairStep = $repairResult['step'];
                    $repairFailArray[] = array(
                        'jobcode' => $jobcode,
                        'msg' => $repairMsg,
                        'step' => $repairStep,
                    );
                    echo $repairMsg;
                }
                echo "=============================== <br>";
            }
            ?>
            <br>
            <div class="bg-info">
                Total Success Repair : <?php echo $repairSuccessCnt; ?><br>
                Total Fail Repair : <?php echo $repairFailCnt; ?><br>
                Detail Fail Repair :<br>
                <?php
                if (!empty($repairFailArray)) {
                    printtable($repairFailArray, "yes");
                } else {
                    echo "No Fail Records <br>";
                }
                ?>
            </div>
            <br>
            <?php
            echo "BEGIN CHECKING JOBCODESID RECORDS <br>";
            $jcSIDList = array();
            foreach ($duplicateSchList as $duplicateSchDataRow2) {
                $jobcode2 = $duplicateSchDataRow2['jobcode'];
                $sid2 = $duplicateSchDataRow2['sid'];
                $qno2 = $duplicateSchDataRow2['qno'];
                $cid2 = $duplicateSchDataRow2['cid'];
                $bid2 = $duplicateSchDataRow2['bid'];
                $runno2 = $duplicateSchDataRow2['rno'];
                $nopos2 = $duplicateSchDataRow2['npos'];
                $checkJCSID2 = checkJCSID($jobcode2);
                if ($checkJCSID2 == 'empty') {
                    $jcSIDList[] = array(
                        'jobcode' => $jobcode2,
                        'remark' => 'no jobcodesid record'
                    );
                } else {
                    $jcsid2_period = $checkJCSID2['period'];
                    $jcsid2_sid = $checkJCSID2['sid'];
                    $jcsidSchDtl = get_schedulingDetailsBySID($jcsid2_period, $jcsid2_sid);
                    if ($jcsidSchDtl == 'empty') {
                        $jcSIDList[] = array(
                            'jobcode' => $jobcode2,
                            'remark' => 'Jobcodesid not pointed correctly'
                        );
                    } else {
                        if ($jcsidSchDtl['quono'] == $qno2 && $jcsidSchDtl['runningno'] == $runno2 && $jcsidSchDtl['noposition'] == $nopos2 && $jcsidSchDtl['cid'] == $cid2 && $jcsidSchDtl['bid'] == $bid2) {

                            $jcSIDList[] = array(
                                'jobcode' => $jobcode2,
                                'in jobcodesid' => 'Jobcodesid pointed correctly',
                            );
                        }
                    }
                }
            }
            printtable($jcSIDList,"yes");
            ?>
        </div>
    </body>
</html>

<?php

function repairDuplicateRecords($period, $sid, $qno, $cid, $bid, $runno, $nopos) {
    try {
        echo "<b> STEP 1 - Make sure the scheduling is correct</b><br>";
//    echo "SID = $sid<br>";
//    echo "Period = $period<br>";
//    echo "Runningno = $runno<br>";
//    echo "noposition = $nopos<br>";
//    echo "cid = $cid<br>";
//    echo "bid = $bid<br>";
        echo "<div class='border border-warning'>";
        $schdatarow = get_schedulingDetailsBySID($period, $sid);
        if ($schdatarow['quono'] == $qno && $schdatarow['runningno'] == $runno && $schdatarow['noposition'] == $nopos && $schdatarow['cid'] == $cid && $schdatarow['bid'] == $bid) {
            echo "Scheduling fetch is correct! can continue to Step 2<br>";
//            echo "Creating Jobcode .....<br>";
            $sch_quono = $schdatarow['quono'];
            $sch_jlfor = $schdatarow['jlfor'];
            $sch_rno = sprintf("%04d", $schdatarow['runningno']);
            $sch_npos = sprintf("%02d", $schdatarow['noposition']);
            $sch_cocode = substr($sch_quono, 0, 3);
            $sch_quoissdt = substr($sch_quono, 4, 4);
            $sch_jobcode = "$sch_jlfor $sch_cocode $sch_quoissdt $sch_rno $sch_npos";
//            echo "JOBCODE = $sch_jobcode<br>";
            $step2 = true;
        } else {
//            echo "Scheduling fetch is not correct! Cannot continue!<br>";
            $step2 = false;
            Throw new Exception('Scheduling fetch is not correct! Cannot continue', 1);
        }
        echo "</div>";
        echo "<br>";

        if ($step2) {
            echo "<b> STEP 2 - Check the correct period placement</b><br>";
            $date_issue = $schdatarow['date_issue'];
            $di_ye = substr($date_issue, 2, 2);
            $di_mo = substr($date_issue, 5, 2);
            $predictedPeriod = sprintf("%02d", $di_ye) . sprintf("%02d", $di_mo);
            $periodSet = get_beforeafterperiod($period);
            $nextPeriod = $periodSet['nextPeriod'];
//            echo "predicted period to place at : $predictedPeriod<br>";
            echo "Checking data in jobcodesid.<br>";
            echo "<div class='border border-warning'>";
            $chkJCSID = checkJCSID($sch_jobcode);
            if ($chkJCSID == 'empty') {
                echo "Jobcodesid cannot be found.<br>";
//                echo "Try comparing to nextperiod<br>";
                $nextschDetail = check_schRecordByPeriod($nextPeriod, $qno, $cid, $bid, $runno, $nopos);
                if ($nextschDetail == 'empty') {
//                    echo "There's no scheduling record found in $nextPeriod<br>";
//                    echo "Is this really duplicate? Cannot continue!<br>";
                    $step2b = FALSE;
                    $step3 = FALSE;
                    Throw new Exception("There's no scheduling record found in $nextPeriod<br>
                    Is this really duplicate? Cannot continue!<br>", 2);
                } else {
                    $nextschdatarow = $nextschDetail['data'];
                    $next_date_issue = $nextschdatarow['date_issue'];
                    $ndi_ye = substr($next_date_issue, 2, 2);
                    $ndi_mo = substr($next_date_issue, 5, 2);
                    $nextpredictedPeriod = sprintf("%02d", $ndi_ye) . sprintf("%02d", $ndi_mo);
                    if ($nextpredictedPeriod == $predictedPeriod) {
                        $correctPeriod = $predictedPeriod;
                        echo "Correct Period is found! ($correctPeriod)<br>";
                        echo "Can go to Step 3<br>";
                        $step2b = FALSE;
                        $step3 = TRUE;
                    } else {
//                        echo "Date issue in scheduling $period and $nextPeriod is not the same.<br>";
//                        echo "Must use different method for detection, go to Step 2.b first<br>";
                        $step2b = TRUE;
                        $step3 = TRUE;
//                    Throw new Exception("There's no scheduling record found in $nextPeriod<br>
//                    Is this really duplicate? Cannot continue!<br>", 2);
                    }
                }
            } else {
                echo "Jobcodesid record is found...<br>";
                if ($chkJCSID['period'] == $predictedPeriod) {
                    $correctPeriod = $predictedPeriod;
                    echo "Correct Period is found! ($correctPeriod)<br>";
                    echo "Can go to Step 3<br>";
                    $step2b = FALSE;
                    $step3 = TRUE;
                } else {
//                    echo "Period recorded in jobcodesid is not the same as prediction.<br>";
//                    echo "Try comparing to nextperiod<br>";
                    $nextschDetail = check_schRecordByPeriod($nextPeriod, $qno, $cid, $bid, $runno, $nopos);
                    if ($nextschDetail == 'empty') {
//                        echo "There's no scheduling record found in $nextPeriod<br>";
//                        echo "Is this really duplicate? Cannot continue!<br>";
                        $step2b = FALSE;
                        $step3 = FALSE;
                        Throw new Exception("There's no scheduling record found in $nextPeriod<br>
                    Is this really duplicate? Cannot continue!<br>", 2);
                    } else {
                        $nextschdatarow = $nextschDetail['data'];
                        $next_date_issue = $nextschdatarow['date_issue'];
                        $ndi_ye = substr($next_date_issue, 2, 2);
                        $ndi_mo = substr($next_date_issue, 5, 2);
                        $nextpredictedPeriod = sprintf("%02d", $ndi_ye) . sprintf("%02d", $ndi_mo);
                        if ($nextpredictedPeriod == $predictedPeriod) {
                            $correctPeriod = $predictedPeriod;
                            echo "Correct Period is found! ($correctPeriod)<br>";
                            echo "Can go to Step 3<br>";
                            $step2b = FALSE;
                            $step3 = TRUE;
                        } else {
//                            echo "Date issue in scheduling $period and $nextPeriod is not the same.<br>";
//                            echo "Must use different method for detection, go to Step 2.b first<br>";
                            $step2b = TRUE;
                            $step3 = TRUE;
                        }
                    }
                }
            }

            echo "</div><br>";
        }

        if ($step2b) {
            echo "<div class='bg-info'>";
            echo "<h4>IF YOU'RE SEEING THIS, MEANS THAT CANNOT FIND CORRECT PERIOD BECAUSE DATE ISSUE IN $period and $nextPeriod IS NOT MATCHING.<br>"
            . "FUNCTION TO GET NOT YET FINISHED, ADD TO THIS LATER<br>"
            . "FOR NOW CANNOT CONTINUE FUNCTION.</h4><br>";
            $step3 = FALSE; // REMOVE THIS IF FUNCTION IS FINISHED
            echo "</div><br>";
            Throw new Exception("Cannot find correct period", 2);
        }

        if ($step3) {
            echo "<b>STEP 3 - Repairing Duplicates</b><br>";
            $targetPeriod = $correctPeriod;
            if ($targetPeriod == $period) {
                $sourcePeriod = $nextPeriod;
            } elseif ($targetPeriod == $nextPeriod) {
                $sourcePeriod = $period;
            }
            echo "CORRECT PERIOD = $targetPeriod<br>";
            echo "DUPLICATE PERIOD = $sourcePeriod<br>";
            echo "<div class='border border-success'>";
            $targetSchTab = "production_scheduling_$targetPeriod";
            $sourceSchTab = "production_scheduling_$sourcePeriod";
            $targetOutTab = "production_output_$targetPeriod";
            $sourceOutTab = "production_output_$sourcePeriod";
//            echo "<div class='border border-warning'>";
//            echo "Check output data from $targetSchTab <br>";
            $targetSchDetail = check_schRecordByPeriod($targetPeriod, $qno, $cid, $bid, $runno, $nopos);
            if ($targetSchDetail != 'empty') {
                $targetSchDataRow = $targetSchDetail['data'];
                $target_sid = $targetSchDataRow['sid'];
                $correct_sid = $target_sid;
            }
            $sourceSchDetail = check_schRecordByPeriod($sourcePeriod, $qno, $cid, $bid, $runno, $nopos);
            if ($sourceSchDetail != 'empty') {
                $sourceSchDataRow = $sourceSchDetail['data'];
                $source_sid = $sourceSchDataRow['sid'];
            }
            if ($targetSchDetail == 'empty') {
//                echo "Cannot find data from $targetSchTab<br>";
//                echo "Process cannot be continued!<br>";
                $step4 = FALSE;
                Throw new Exception("Cannot find data from $targetSchTab<br>
                    Process cannot be continued!<br>", 3);
            } else {
//                echo "<div class='border border-danger'>";
                $target_outDetail = check_outputRecordbySID($targetPeriod, $target_sid);
                if ($target_outDetail == 'empty') {
//                    echo "<div class='border border-light'>";
//                    echo "Output is empty, begin check if stray input to $sourceOutTab or not<br>";
                    $stray_outputdataset = getStrayOutputRecord($sourcePeriod, $target_sid, $qno, $runno, $nopos, $cid);
                    if ($stray_outputdataset != 'empty') {
//                        echo "Found stray input, fix this and move it into $targetOutTab.<br>";
                        MoveStrayOutputRecord($stray_outputdataset, $sourcePeriod, $targetPeriod);
                    } else {
//                        echo "Cannot find any stray input.<br>";
//                        echo "<div class='border border-info'>";
//                        echo "Begin check from $sourceSchTab to see if there's output record or not<br>";
                        if ($sourceSchDetail != 'empty') {
//                            echo "Found data in $sourceSchTab<br>";
//                            echo "Source SID = $source_sid<br>";
                            $source_outDetail = check_outputRecordbySID($sourcePeriod, $source_sid);
                            if ($source_outDetail == 'empty') {
//                                echo "There's no record in $sourceOutTab.<br>";
//                                echo "There's no record in $sourceOutTab and $targetOutTab<br>";
//                                echo "Joblist might not yet be scanned. No need for Output<br>";
//                                echo "Moving on.<br>";
                            } else {
//                                echo "Found record in $sourceOutTab<br>";
//                                echo "Converting SID from $source_sid to $target_sid to point to $targetSchTab<br>";
                                $source_outdataset = $source_outDetail['data'];
                                foreach ($source_outdataset as $source_outkey => $source_outdatarow) {
                                    $source_outdataset[$source_outkey]['sid'] = $target_sid;
                                }
//                                echo "Table Converted :";
//                                printtable($source_outdataset . "yes");
//                                echo "Move output record int $targetOutTab<br>";
                            }
                        }
//                        echo"</div>"; //check output duplicate transfer
                    }
//                    echo "</div>"; //check stray output records
                } else {
//                    echo "Output exists.<br>";
                }
//                echo "</div>"; //end check output
//                echo "==Finished checking output data==<br>";
//                echo "==Begin deleting duplicate records == <br>";
//                echo "DUPLICATE PERIOD  = $sourcePeriod<br>";
//                echo "<div class='bg-danger'>";
                echo "<div class='border border-light'>";
                echo "Deleting duplicate output records...<br>";
                $qrDelDupOut = "DELETE FROM $sourceOutTab WHERE sid = $source_sid";
                $objSQLDelDupOut = new SQL($qrDelDupOut);
                $ResultDelDupOut = $objSQLDelDupOut->getDelete();
                display_codeblock($qrDelDupOut);
                echo "Delete Output Result : $ResultDelDupOut<br>";
                echo "</div>";
                echo "<div class='border border-light'>";
                echo "Deleting duplicate Scheduling records...<br>";
                $qrDelDupSch = "DELETE FROM $sourceSchTab WHERE sid = $source_sid AND quono = '$qno' AND runningno = $runno AND cid = $cid AND noposition = $nopos AND bid = $bid";
                $objSQLDelDupSch = new SQL($qrDelDupSch);
                $ResultDelDupSch = $objSQLDelDupSch->getDelete();
                display_codeblock($qrDelDupSch);
                echo "Delete Scheduling Result : $ResultDelDupSch<br>";
                echo "</div>";
//                echo "</div>";
                $step4 = TRUE;
            }
//            echo "</div>"; //
            echo "</div><br>";
        }

        if ($step4) {
            echo "<b>STEP 4 - Final JOBCODESID Check</b><br>";
            echo "JOBCODE = $sch_jobcode<br>";
            echo "<div class='bg-info'>";
            if ($chkJCSID == 'empty') {
                echo "There was no record in Jobcodesid for, no need to do check<br>";
            } else {
//                printtable($chkJCSID);
                $finjcsid_sid = $chkJCSID['sid'];
                $finjcsid_period = $chkJCSID['period'];
                if ($correct_sid == $finjcsid_sid) {
                    echo "SID Recorded is correct!<br>";
                } else {
                    echo "SID Recorded is wrong!, Begin update it.<br>";
                    $qrUpdJC1 = "UPDATE jobcodesid SET sid = $correct_sid WHERE jobcode = '$sch_jobcode'";
                    $objSQLUpdJC1 = new SQL($qrUpdJC1);
                    $resultUpdJC1 = $objSQLUpdJC1->getUpdate();
                    echo "<b>SID Update result = $resultUpdJC1</b>";
                }
                if ($correctPeriod == $finjcsid_period) {
                    echo "Period Recorded is correct!<br>";
                } else {
                    echo "Period Recorded is wrong!, Begin update it.<br>";
                    $qrUpdJC2 = "UPDATE jobcodesid SET period = '$correctPeriod' WHERE jobcode = '$sch_jobcode'";
                    $objSQLUpdJC2 = new SQL($qrUpdJC2);
                    $resultUpdJC2 = $objSQLUpdJC2->getUpdate();
                    echo "<b>Period Update result = $resultUpdJC2</b>";
                }
            }
            echo "</div><br>";
            $step5 = TRUE;
        }

        if ($step5) {
            echo "<b>STEP 5 - Check if still duplicate or not</b><br>";
            echo "Correct Period = $targetPeriod<br>";
            echo "Duplicate Period = $sourcePeriod<br>";
            $finalDupChkSch = check_schRecordByPeriod($sourcePeriod, $qno, $cid, $bid, $runno, $nopos);
            if ($finalDupChkSch == 'empty') {
                echo "<p class='bg-success'><h4>NO DUPLICATE IS FOUND.</h4></p>";
            } else {
                echo "<p class='bg-danger'><h4>DUPLICATE IS STILL FOUND.</h4></p>";
                Throw new Exception("Data is still duplicate!", 5);
            }
        }
        $repairResult = array('status' => 'success');
    } catch (Exception $e) {
        $repairResult = array('status' => 'fail', 'msg' => $e->getMessage(), 'step' => $e->getCode());
    }
    return $repairResult;
}

function get_schedulingDetailsBySID($period, $sid) {
    $schtab = "production_scheduling_$period";
//    echo "====Fetch scheduling on $schtab, SID = $sid====<br>";
    $qrsch = "SELECT * FROM $schtab WHERE sid = $sid";
    $objSQLsch = new SQL($qrsch);
    $resultsch = $objSQLsch->getResultOneRowArray();
    if (!empty($resultsch)) {
//        printtable($resultsch, 'no');
//        echo "====End fetch scheduling on $schtab, SID = $sid====<br>";
        return $resultsch;
    } else {
//        echo "Cannot find scheduling records....<br>";
//        echo "====End fetch scheduling on $schtab, SID = $sid====<br>";
        return 'empty';
    }
}

function checkJCSID($jobcode) {
//    echo "=== BEGIN CHECKING JOBCODESID for JOBCODE  = $jobcode=== <br>";
//    $tab = 'jobcodesid';
    $qr = "SELECT * FROM jobcodesid WHERE jobcode = '$jobcode' ORDER BY jcodeid DESC";
    $objSQL = new SQL($qr);
    $result = $objSQL->getResultOneRowArray();
    if (!empty($result)) {
//        echo "Found jobcodesid record<br>";
//        printtable($result, 'no');
//        echo "=== END CHECKING JOBCODESID for JOBCODE  = $jobcode=== <br>";
        return $result;
    } else {
//        echo "There's no jobcodesid record for $jobcode<br>";
//        echo "This jobcode hasn't been scanned yet<br>";
//        echo "=== END CHECKING JOBCODESID for JOBCODE  = $jobcode=== <br>";
        return 'empty';
    }
}

function MoveStrayOutputRecord($outputdataset, $sourceperiod, $targetperiod) {
    echo "===Moving Output Record from $sourceperiod to $targetperiod<br>";
    $targetpottab = "production_output_$targetperiod";
    $sourcepottab = "production_output_$sourceperiod";
    $qrInsDebug = '';
    $qrInsAll = '';
    foreach ($outputdataset as $outputdatarow) {
        $sid = $outputdatarow['sid'];
        unset($outputdatarow['poid']);
        $qrIns = "INSERT INTO $targetpottab SET ";
        $countArr = count($outputdatarow);
        $cnt = 0;
        foreach ($outputdatarow as $potkey => $potval) {
            $cnt++;
            $qrIns .= " $potkey = '$potval' ";
            if ($cnt != $countArr) {
                $qrIns .= ' , ';
            }
        }
        $qrIns .= ";";
        $qrInsAll .= $qrIns;
        $qrInsDebug .= "$qrIns<br>";
    }
//    display_codeblock($qrInsDebug);
    $objSQLIns = new SQL($qrInsAll);
    $insResult = $objSQLIns->InsertData();
    echo "Result = $insResult<br>";
    if ($insResult != 'insert ok!') {
        echo "<p class='bg-danger'>Warning, Failed to insert record</p>";
    } else {
        echo "Delete Source Record<br>";
        $qrDel = "DELETE FROM $sourcepottab WHERE sid = $sid";
        $objSQLDel = new SQL($qrDel);
        $delResult = $objSQLDel->getDelete();
        echo "Delete result = $delResult<br>";
    }
}

function getStrayOutputRecord($period, $sid, $qno, $rno, $npos, $cid) {
    $pottab = "production_output_$period";
//    echo "Try checking for output data in $pottab<br>";
    $qrpot = "SELECT * FROM $pottab WHERE sid = $sid";
    $objSQLpot = new SQL($qrpot);
    $potdataset = $objSQLpot->getResultRowArray();
//    display_codeblock($qrpot);
    if (!empty($potdataset)) {
//        echo "Found record in $pottab with sid = $sid<br>";
//        echo "Check Scheduling in $period if it matches or not<br>";
        $schtab = "production_scheduling_$period";
        $qrschchk = "SELECT * FROM $schtab WHERE sid = $sid";
        $objSQLschchk = new SQL($qrschchk);
        $schchk1dataset = $objSQLschchk->getResultRowArray();
//        display_codeblock($qrschchk);
        if (empty($schchk1dataset)) {
//            echo "No Scheduling is found, Output records are stray<br>";
            $retoutput = true;
        } else {
//            echo "Found record !<br>";
//            printtable($schchk1dataset, 'yes');
            $qrschchk2 = "SELECT COUNT(*) FROM $schtab WHERE sid = $sid AND quono = '$qno' AND runningno = $rno AND noposition = $npos AND cid = $cid";
            $objSQLschchk2 = new SQL($qrschchk2);
            $schchk2numrow = $objSQLschchk2->getRowCount();
//            display_codeblock($qrschchk2);
            if ($schchk2numrow == 0) {
//                echo "This is a different scheduling record, output belong here<br>";
                $retoutput = false;
            } else {
//                echo "This is the exact same record.<br>";
                $retoutput = true;
            }
        }
    } else {
//        echo "Not found any record in $pottab.<br>";
        $retoutput = false;
    }
    if ($retoutput) {
        return $potdataset;
    } else {
        return 'empty';
    }
}

function check_outputRecordbySID($period, $sid) {
    $pottab = "production_output_$period";
//    echo "===Checking $pottab for SID = $sid===<br>";
    $qrpotcount = "SELECT COUNT(*) FROM $pottab WHERE sid = $sid AND jobtype != 'jobtake'";
    $objSQLpotcount = new SQL($qrpotcount);
    $potnumrow = $objSQLpotcount->getRowCount();
//    display_codeblock($qrpotcount);
//    echo "Found $potnumrow Records<br>";
    if ($potnumrow <= 0) {
//        echo "Cannot find any record <br>";
//        echo "===End Checking $pottab for SID = $sid ===<br>";
        return 'empty';
    } else {
//        echo "Found Record! <br>";
        $qrpot = "SELECT * FROM $pottab WHERE sid = $sid AND jobtype != 'jobtake'";
        $objSQLpot = new SQL($qrpot);
        $potdatarow = $objSQLpot->getResultRowArray();
//        printtable($potdatarow, 'yes');
//        echo "===End Checking $pottab for SID = $sid ===<br>";
        return array('count' => $potnumrow, 'data' => $potdatarow);
    }
}

function check_schRecordByPeriod($period, $qno, $cid, $bid, $runno, $nopos) {
    $schtab = "production_scheduling_$period";
//    echo "===Checking $schtab for quono = $qno;cid = $cid;bid = $bid;runno = $runno;noposition = $nopos======<br>";
//    $qrschcount = "SELECT COUNT(*) FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos AND status != 'cancelled'";
    $qrschcount = "SELECT COUNT(*) FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos";
    $obJSQLschcount = new SQL($qrschcount);
    $schnumrow = $obJSQLschcount->getRowCount();
    display_codeblock($qrschcount);
//    echo "Found $schnumrow scheduling records<br>";
    if ($schnumrow <= 0) {
//        echo "Cannot find any record<br>";
//        echo "===End $schtab for quono = $qno;cid = $cid;bid = $bid;runno = $runno;noposition = $nopos======<br>";
        return 'empty';
    } else {
//        echo "Found record!<br>";
//        $qrsch = "SELECT * FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos AND status != 'cancelled'";
        $qrsch = "SELECT * FROM $schtab WHERE quono = '$qno' AND cid = $cid AND bid = $bid AND runningno = '$runno' AND noposition = $nopos";
        $obJSQLsch = new SQL($qrsch);
        $schdatarow = $obJSQLsch->getResultOneRowArray();
//        printtable($schdatarow, "no");
//        echo "===End $schtab for quono = $qno;cid = $cid;bid = $bid;runno = $runno;noposition = $nopos======<br>";
        $out = array('count' => $schnumrow, 'data' => $schdatarow);
        return $out;
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
    echo "<div > ";
    if ($multirow == 'yes') {
        echo "<table class='table table-responsive table-bordered bg-dark'>";
        echo "<tr >";
        foreach ($array[0] as $key => $val) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        foreach ($array as $array_row) {
            echo "<tr>";
            foreach ($array_row as $key => $val) {
                echo "<td>$val</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<table  class='table table-responsive table-bordered bg-dark' >";
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
    echo "<pre style='background-color:lightgray;color:black;height:50px;max-height:100px;max-width:700px;width:700px;overflow-x:auto'>$qr</pre>";
}
?>


