<!DOCTYPE html>
<html lang="en">

<head>
    <?php $tmpl->dumpHeaders(); ?>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $tmpl->title; ?> - <?php echo __SITENAME; ?></title>
    <link href="<?php echo $tmpl->url ?>/assets/img/brand/puzzleos_ic.png" rel="icon" type="image/png">
    <link href="<?php echo $tmpl->url ?>/assets/css/argon-dashboard.min.css?v=1.1.0" rel="stylesheet" />
    <link href="<?php echo $tmpl->url ?>/assets/fonts/nhv.css" rel="stylesheet" />
    <?php ob_start() ?>
    <style>
        label input:first-of-type {
            margin-right: 15px;
        }

        html,
        body,
        .mh {
            font-family: 'Neue Helvetica', sans-serif;
            height: 100%;
        }

        @media(min-width:768px) {
            .showdesk {
                display: block !important
            }
        }

        @media(max-width:768px) {
            body {
                height: calc(100% - 72px) !important;
            }
        }
    </style>
    <?php echo Minifier::outCSSMin() ?>
</head>

<body>
    <div class="showdesk" style="display:none;float:right;position: fixed;top: 0;right: 20px;z-index: 999;"><?php $tmpl->navigation->loadView("login_bar"); ?></div>
    <nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand pt-0" href="/">
                <img src="<?php echo $tmpl->url ?>/assets/img/brand/puzzleos.png" class="navbar-brand-img" alt="...">
            </a>
            <ul class="nav align-items-center d-md-none">
                <li class="nav-item dropdown">
                    <?php if (PuzzleUser::isAccess(USER_AUTH_REGISTERED)) : ?>
                    <a class="nav-link" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <div class="media align-items-center">
                            <span class="avatar avatar-sm rounded-circle">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-right">
                        <?php $tmpl->navigation->loadView("login_bar_dropdown") ?>
                    </div>
                    <?php endif ?>
                </li>
            </ul>
            <div class="collapse navbar-collapse" id="sidenav-collapse-main">
                <div class="navbar-collapse-header d-md-none">
                    <div class="row">
                        <div class="col-6 collapse-brand">
                            <a href="/">
                                <img src="<?php echo $tmpl->url ?>/assets/img/brand/puzzleos.png">
                            </a>
                        </div>
                        <div class="col-6 collapse-close">
                            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#sidenav-collapse-main" aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle sidenav">
                                <span></span>
                                <span></span>
                            </button>
                        </div>
                    </div>
                </div>
                <ul class="navbar-nav">
                    <?php $tmpl->navigation->loadView("left") ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="main-content mh">
        <div class="container-fluid mt--7 mh" style="margin-top: 0rem !important;padding-top: 2rem !important;padding-bottom: 2rem !important;">
            <?php
            if ($tmpl->http_code == 200) $tmpl->app->loadMainView();
            else if ($tmpl->http_code == 403) {
                if (PuzzleUser::isAccess(USER_AUTH_REGISTERED)) {
                    include("404.php");
                } else
                    redirect("/users?redir=/" . urlencode(__HTTP_REQUEST));
            } else include("404.php");
            ?>
        </div>
    </div>
    <?php echo $tmpl->postBody ?>
    <?php Prompt::printPrompt() ?>
</body>

</html>