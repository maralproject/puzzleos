<?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif ?>
<div style="height:100%;width:100%;display:flex;align-items: center;justify-content: center;">
    <div style="max-width:400px;width:calc(100% - 30px);">
        <div id="loginCtn">
            <div style="font-weight:300;margin-bottom:20px;">
                <span style="font-size:20pt;font-weight:500;">Login</span>
            </div>
            <form action="/users/login" method="post" style="text-align:center;">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                    </div>
                    <input maxlength="50" required name="user" autocomplete="username" autocapitalize="none" type="text" class="es form-control" placeholder="E-mail, or Phone">
                </div><br>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-key"></i></span>
                    </div>
                    <input maxlength="50" required name="pass" autocomplete="off" type="password" class="es form-control" placeholder="Password">
                </div>
                <div class="invalid-feedback ff"></div>
                <br>
                <?php $_csrf() ?>
                <input type="hidden" name="redir" value="<?php h($_GET["redir"]) ?>">
                <?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
                <button type="submit" class="btn btn-primary g-recaptcha" data-sitekey="<?php h(PuzzleUserConfig::recaptchaSitekey()) ?>" data-callback="__doLogin">Log In</button>
                <?php else : ?>
                <button type="submit" class="btn btn-primary">Log In</button>
                <?php endif ?>
            </form><br>

            <div class="helpform" style="text-align:center;">
                <a href="/users/forgot">Forgot Password</a>
                <?php if (PuzzleUserConfig::allowRegistration()) : ?>
                <br>Don't have an account? <b><a href="/users/signup">Sign Up</a></b>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>
<?php include("otp.php") ?>
<?php ob_start() ?>
<script>
    function __doLogin(r) {
        $("#loginCtn form").submit();
    }
    $("#loginCtn input").filter(function() {
        return this.value == "";
    })[0].focus();
    $("#loginCtn form").on("submit", function(e) {
        e.preventDefault();
        let f = $(this);
        let s = f.serialize();
        let p = m => {
            f.find('input.es').addClass("is-invalid");
            f.find('.ff').text(m).show();
        };
        f.find("*").prop("disabled", true);
        $.post("/users/login", s, d => {
            <?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
            grecaptcha.reset();
            <?php endif ?>
            if (d.loggedin) location.replace(d.redir || '/');
            else {
                if (d.challenge_needed) {
                    $("#verifyOTP").xmodal(m => {
                        m.on('shown.bs.modal', function() {
                            $(this).find("input").focus();
                        }).on('hide.bs.modal', () => {
                            f.find("*").prop("disabled", false);
                        });
                        m.find(".modal-title").text("Two-Factor Authentication");
                        let l = d.token.method == "email" ? `Type the code we've just sent to your email at ${d.token.recipient}.` : (d.token.method == "sms" ? `Type the code we've just sent to your phone at ${d.token.recipient}.` : "Type the code on your authenticator app.");
                        m.find("small").text(l);
                        m.find("button[type=submit]").text("Verify");
                        m.find("form").submit(e => {
                            e.preventDefault();
                            let f = $(e.target).serialize() + `&session=${d.token.hash}`;
                            $(e.target).find("*").prop("disabled", true);
                            $.post("/users/verify", f, r => {
                                if (r) {
                                    m.modal("hide");
                                    location.replace(d.redir || '/');
                                } else {
                                    $(e.target).find("*").prop("disabled", false);
                                    m.find(".invalid-feedback").text("The code you entered is wrong.").show();
                                    m.find("input[name=code]").addClass("is-invalid")[0].select();
                                }
                            }).fail(function() {
                                $(e.target).find("*").prop("disabled", false);
                                showMessage("An error occured", "danger");
                            });
                        });
                    });
                } else {
                    f.find("*").prop("disabled", false);
                    p("We could not find this account");
                }
            }
        }).fail(function(err) {
            <?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
            grecaptcha.reset();
            <?php endif ?>
            f.find("*").prop("disabled", false);
            $("#loginCtn input[name=user]")[0].select();
            $("#loginCtn input[name=pass]")[0].value = "";
            p("We could not find this account");
        });
    });
</script>
<?php echo Minifier::outJSMin() ?>