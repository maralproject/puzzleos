<?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif ?>
<div style="height:100%;width:100%;display:flex;align-items: center;justify-content: center;">
    <div style="max-width:400px;width:calc(100% - 30px);">
        <div id="loginCtn" class="signupCtn">
            <div style="font-weight:300;margin-bottom:20px;">
                <span style="font-size:20pt;font-weight:500;">Sign Up</span>
            </div>
            <form action="/users/signup" method="post" style="text-align:center;">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                    </div>
                    <input autofocus maxlength="50" name="fullname" autocomplete="off" autocapitalize="none" type="text" class="form-control" placeholder="Full Name">
                </div><br>
                <?php if (PuzzleUserConfig::emailRequired()) : ?>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                    </div>
                    <input name="email" type="email" autocomplete="off" autocapitalize="none" class="form-control" placeholder="E-mail Address">
                </div><br>
                <div class="invalid-feedback"></div>
                <?php endif ?>
                <?php if (PuzzleUserConfig::phoneRequired()) : ?>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                    </div>
                    <input name="phone" pattern="^[0-9\+]{8,15}$" autocomplete="off" autocapitalize="none" class="form-control" placeholder="Phone Number">
                </div><br>
                <?php endif ?>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-key"></i></span>
                    </div>
                    <input maxlength="50" name="password" autocomplete="off" autocapitalize="none" type="password" class="form-control" placeholder="New Password">
                </div>
                <div class="error-place"></div>
                <br>
                <?php $_csrf() ?>
                <input type="hidden" name="redir" value="<?php h($_GET["redir"]) ?>">
                <?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
                <button type="submit" class="btn btn-primary g-recaptcha" data-sitekey="<?php h(PuzzleUserConfig::recaptchaSitekey()) ?>" data-callback="__doSignUp">Sign Up</button>
                <?php else : ?>
                <button type="submit" class="btn btn-primary">Sign Up</button>
                <?php endif ?>
            </form><br><br>

            <div class="helpform" style="text-align:center;">
                Have an account? <b><a href="/users">Log In</a></b>
            </div>
        </div>
    </div>
</div>

<?php include "otp.php" ?>

<?php ob_start() ?>
<script>
    function __doSignUp(r) {
        $("#loginCtn form").submit();
    }
    $("#loginCtn form").submit(e => {
        e.preventDefault();
        let f = $(e.target),
            a;
        f.find(".is-invalid").removeClass("is-invalid");
        f.find(".invalid-feedback").remove();
        if ((a = f.find("input[name=fullname]")).val() == "") {
            a.addClass('is-invalid');
            $(`<div class="invalid-feedback">Fullname cannot be empty</div>`).insertAfter(a.parent()).show();
        }
        if ((a = f.find("input[name=email]")).length) {
            let t = (/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/).test(a.val());
            if (!t) {
                a.addClass('is-invalid');
                $(`<div class="invalid-feedback">E-mail you entered is not valid.</div>`).insertAfter(a.parent()).show();
            }
        }
        if ((a = f.find("input[name=phone]")).length) {
            let t = (/^[0-9\+]{8,15}$/).test(a.val());
            if (!t) {
                a.addClass('is-invalid');
                $(`<div class="invalid-feedback">Phone number you entered is not valid.</div>`).insertAfter(a.parent()).show();
            }
        }
        if ((a = f.find("input[name=password]")).val() == "") {
            a.addClass('is-invalid');
            $(`<div class="invalid-feedback">Password cannot be empty.</div>`).insertAfter(a.parent()).show();
        }
        if ((a = f.find(".is-invalid")).length < 1) {
            let s = f.serialize();
            f.find("*").prop("disabled", true);
            $.post("/users/signup1", s, d => {
                <?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
                grecaptcha.reset();
                <?php endif ?>
                f.find("*").prop("disabled", false);
                if (d.need_activation) {
                    $("#verifyOTP").xmodal(m => {
                        m.on('shown.bs.modal', function() {
                            $(this).find("input").focus();
                        });
                        m.find(".modal-title").text("Verify your Account");
                        let l = d.token.method == "email" ? `Type the code we've just sent to your email at ${d.token.recipient}.` : (d.token.method == "sms" ? `Type the code we've just sent to your phone at ${d.token.recipient}.` : "Type the code on your authenticator app.");
                        m.find("small").text(l);
                        m.find("form").submit(e => {
                            e.preventDefault();
                            let f = $(e.target).serialize() + `&session=${d.token.hash}`;
                            $(e.target).find("*").prop("disabled", true);
                            $.post("/users/signup2", f, r => {
                                if (r) {
                                    location.replace(d.redir || '/');
                                    m.modal('hide');
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
                    location.replace(d.redir || '/');
                }
            }).fail(e => {
                <?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
                grecaptcha.reset();
                <?php endif ?>
                f.find("*").prop("disabled", false);
                $(`<div class="invalid-feedback">Either phone or e-mail address has been used.</div>`).appendTo(".error-place").show();
            });
        } else {
            a[0].select();
        }
    });
</script>
<?php echo Minifier::outJSMin() ?>