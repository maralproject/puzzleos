<?php ob_start()?>
<style>
    html, body{
        width:100%;
        height:100%;
    }
    html, body, h1, h2, h3, h4, h5, h6{
        font-family: Roboto, sans-serif;
    }
    body{
        padding:0px 20px;
    }
    .form-control:focus{
        box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 0px 2px rgba(102,175,233,0.6);
        webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 0px 2px rgba(102,175,233,0.6);
    }
    .form-control, .input-group{
        border-radius:7px;
    }
</style>
<?php echo Minifier::outCSSMin()?>