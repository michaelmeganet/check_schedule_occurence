<!DOCTYPE html>
<html lang="en">
    <!--   -->
    <head>
        <title>ISHIN Japanese Dining</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <!--https://en.wikipedia.org/wiki/Meta_refresh-->

        <!-- SmartMenus core CSS (required) -->
        <link href="assets/css/sm-core-css.css" rel="stylesheet" type="text/css" />

        <!-- "sm-clean" menu theme (optional, you can use your own CSS, too) -->
        <link href="assets/css/sm-clean.css" rel="stylesheet" type="text/css" />

        <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">-->
        <link rel='stylesheet' href='assets/bootstrap-3.3.7/bootstrap-3.3.7/dist/css/bootstrap.min.css'/>

        <link rel="stylesheet" href="./assets/style.css">
        <!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js" ></script>-->
        <script src='bower_components/jquery/dist/jquery.min.js'></script>
        <!--<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>-->
        <script src='assets/bootstrap-3.3.7/bootstrap-3.3.7/dist/js/bootstrap.min.js'></script>
        <script>
            /*
             function cloneValidate(url, index) {
             if (window.confirm("Are you sure you want to clone?\nThis will create a duplicate in your table.")) {
             window.open(url, "_self");
             return false;
             } else {
             window.location.href = index;
             }
             }*/
            function delValidate(url, index) {
                if (window.confirm("Caution! Data will be deleted!\nThis cannot be undone!")) {
                    window.open(url, "_self");
                    return false;
                    //alert(url);
                } else {
                    window.location.href = index;
                }
            }
            /*
             function dtlOpenNewWindow(url) {
             window.open(url, "_self");
             return false;
             }

             function updOpenNewWindow(url) {
             window.open(url, "_self");
             return false;
             }
             function openPopUp(url) {
             window.open(url);
             return false;
             }
             function updateValidate() {
             if (window.confirm("Are you sure you want to submit?")) {
             return true;
             } else {
             return false;
             }
             }
             function redirectToMain(text, main) {
             window.alert(text);
             window.location.href = main;
             }

             function redirectToIndex(text){
             window.alert(text);
             window.location.href = './.php';
             }
             /*var $my_form = $("#updateForm");
             $my_form.validate(function($form, e) {
             if (window.confirm("Are you sure you want to submit?")){
             window.location = index.php;
             }
             })*/
        </script>

    </head>
    <!-- https://www.smartmenus.org/about/themes/
         Complete navbar .sm-clean -->
    <div id="page">
        <div id="content">
            <body  onLoad="">
<!--                <img src='assets/images/Ishin-logo.png' style='max-height:70px'/>-->
                <nav id="main-nav"class='main-nav' role='navigation' >
                    <input id="main-menu-state" type="checkbox" />
                    <label class="main-menu-btn" for="main-menu-state">
                        <span class="main-menu-btn-icon"></span> Toggle main menu visibility
                    </label>
                    <div class="navbar-header">
                        <img src="./assets/images/Ishinlogo.png" alt="ishin" >
                    </div>
                    
                    <!-- Sample menu definition -->
                    <ul id="main-menu" class="sm sm-clean">
                        <li><a href='index.php'>Home</a></li>
                        <li><a href='CRUD-index.php?view=main'>Customer</a>
                            <ul>
                                <li><a href='CRUD-index.php?view=CC'>Create Customer</a></li>
                                <li><a href='CRUD-index.php?view=main'>Customer List</a></li>
                            </ul>
                        </li>
                        <li><a href='form_redeemVoucher.php'>Vouchers</a>
                            <ul>
                                <li><a href='#'>E-Voucher</a>
                                    <ul id='main'>
                                        <li><a href='form_mailCustomer.php'>Create Single</a></li>
                                        <li><a href='form_batchMailCustomer.php'>Create Batch</a></li>
                                    </ul>
                                </li>
                                <li><a href='#'>Pre-Printed Voucher</a>
                                    <ul id='main'>
                                        <li><a href='importPreprintVoucher.php'>Activate Pre-Printed Voucher</a></li>
                                    </ul>
                                </li>
                                <li><a href='form_redeemVoucher.php'>Redeem Voucher</a></li>
                            </ul>
                        </li>
                        <?php
                        if (isset($_SESSION['activeUserCredentials'])) {
                            if ($_SESSION['activeUserCredentials'] == 'admin') {
                                ?>
                                <li><a href='AUTH-index.php'>Administration</a>
                                    <ul>
                                        <li><a href='#'>User Account</a>
                                            <ul id='main'>
                                                <li><a href='AUTH-index.php?Aview=RG'>Register New User</a></li>
                                                <li><a href='AUTH-index.php?Aview=AA'>Activate Google Authenticator</a></li>
                                            </ul>
                                        </li>
                                        <li><a href='AUTH-index.php?Aview=main'>User List</a></li>
                                    </ul>
                                </li>
                                <?php
                            }
                        }
                        ?>
                        <li class="sm-rtl"><a href='logout.php'>Logout </a></li>
                    </ul>
                </nav>
                <?php
                if (isset($_SESSION['activeUser'])) {
                    ?>
                    <div class=" content" style="text-align:left;margin: 0px 40px 0px 40px">
                        <label class="label label-info" style="font-size:12px">Welcome, <?php echo $_SESSION['activeUser'] . ",  " . $_SESSION['activeUserCredentials']; ?></label></div>
                    <br>
                    <?php
                }
                ?>