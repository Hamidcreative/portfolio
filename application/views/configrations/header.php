<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr"> <!DOCTYPE html>
<!-- BEGIN: Head-->
<head>
    <access origin="*" subdomains="true" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="Materialize is a Material Design Admin Template,It's modern, responsive and based on Material Design by Google.">
    <meta name="keywords" content="materialize, admin template, dashboard template, flat admin template, responsive admin template, eCommerce dashboard, analytic dashboard">
    <meta name="author" content="ThemeSelect">
    <title>Inventory System</title>
    <link rel="apple-touch-icon" href="<?=base_url()?>assets/images/favicon/apple-touch-icon-152x152.png">
    <link rel="shortcut icon" type="image/x-icon" href="<?=base_url()?>assets/images/favicon/favicon-32x32.png">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- BEGIN: VENDOR CSS-->
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/vendors/vendors.min.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/vendors/flag-icon/css/flag-icon.min.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/vendors/data-tables/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/vendors/data-tables/extensions/responsive/css/responsive.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/vendors/data-tables/css/select.dataTables.min.css">
    <!-- END: VENDOR CSS-->
    <!-- BEGIN: Page Level CSS-->
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/css/themes/vertical-dark-menu-template/materialize.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/css/themes/vertical-dark-menu-template/style.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/css/pages/data-tables.css">
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/css/jquery.toast.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.7/css/select2.min.css" rel="stylesheet" />
    <!-- END: Page Level CSS-->
    <!-- BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="<?=base_url()?>assets/css/custom/custom.css">
    <!-- END: Custom CSS-->
    <script src="<?=base_url()?>assets/js/jquery-3.4.0.min.js" type="text/javascript"></script>
</head>
<!-- END: Head-->
<body class="vertical-layout page-header-light vertical-menu-collapsible vertical-dark-menu 2-columns  " data-open="click" data-menu="vertical-dark-menu" data-col="2-columns">

<!-- BEGIN: Header-->
<header class="page-topbar" id="header">
    <div class="navbar navbar-fixed">
        <nav class="navbar-main navbar-color nav-collapsible sideNav-lock navbar-light">
            <div class="nav-wrapper">
                <ul class="navbar-list right">
                    <li class="hide-on-med-and-down"><a class="waves-effect waves-block waves-light toggle-fullscreen" href="javascript:void(0);"><i class="material-icons">settings_overscan</i></a></li>
                    <?php if(isAdministrator($this->session->userdata('user')->id)) { ?>
                     <li><a class="waves-effect waves-block waves-light notification-button" href="javascript:void(0);" data-target="notifications-dropdown"><i class="material-icons">notifications_none<small class="notification-badge">
                     <?php if($notifications['minimumlevelstock']) echo '1';?>
                     </small></i></a></li>
                    <?php } ?>
                    <li><a class="waves-effect waves-block waves-light profile-button" href="javascript:void(0);" data-target="profile-dropdown"><span class="avatar-status avatar-online"><img src="<?=checkFilePath($this->session->userdata('user')->avatar)?>" alt="avatar"><i></i></span></a></li>
                </ul>
                 <!-- notifications-dropdown-->
                <?php if(isAdministrator($this->session->userdata('user')->id)) { ?>
                <ul class="dropdown-content" id="notifications-dropdown">
                    <?php if($notifications['minimumlevelstock']) { ?>
                    <li><a class="grey-text text-darken-2" href="<?=base_url('inventory/minlevel')?>"><span class="material-icons icon-bg-circle cyan small">add_shopping_cart</span> Minimum Stock Level Reached</a>
                    </li>
                    <?php } ?>
                  <li class="divider"></li>
                </ul>
                <?php } ?>
                <!-- profile-dropdown-->
                <ul class="dropdown-content" id="profile-dropdown">
                    <li><a class="grey-text text-darken-1" href="<?=base_url('users/'.$this->session->userdata('user')->id)?>"><i class="material-icons">person_outline</i> Profile</a></li>
                    <li><a class="grey-text text-darken-1" href="<?=base_url('auth/logout')?>"><i class="material-icons">keyboard_tab</i> Logout</a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>
<!-- END: Header-->


