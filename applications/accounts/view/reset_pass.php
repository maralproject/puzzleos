<?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif ?>
<div style="height:100%;width:100%;display:flex;align-items: center;justify-content: center;">
    <div style="max-width:400px;width:calc(100% - 30px);">
        <div id="loginCtn">
            <div style="font-weight:300;margin-bottom:20px;">
                <div>
                    <span style="font-size:20pt;font-weight:500;">Forgot Password</span>
                    <div class="text-muted"><small>We will send verification code to either your e-mail or phone.</small></div>
                </div>
            </div>
            <form method="post" style="text-align:center;">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                    </div>
                    <input autocomplete="off" name="user" autofocus type="text" class="form-control" placeholder="E-mail, or Phone" required>
                </div>
                <div class="invalid-feedback">We could not find account matching, nor we can't send you a verification code.</div>
                <br>
                <?php $_csrf() ?>
                <?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
                <button type="submit" class="btn btn-primary g-recaptcha" data-sitekey="<?php h(PuzzleUserConfig::recaptchaSitekey()) ?>" data-callback="__doMyForgot">Reset My Password</button>
                <?php else : ?>
                <button type="submit" class="btn btn-primary">Reset My Password</button>
                <?php endif ?>
            </form>

            <div class="helpform" style="text-align:center;margin-top:50px;">
                Have an account? <b><a href="/users">Log In</a></b>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="verifyOTP">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Change your password</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div style="max-width:600px;margin:auto;">
                        <div class="row">
                            <div class="col-sm-6" style="padding-bottom:15px;">
                                <label style="width:100%">
                                    <span class="title">Verification code</span>
                                    <input required type="text" class="form-control" style="margin-top:5px;" name="code">
                                </label>
                                <div class="invalid-feedback vcw"></div>
                                <div class="text-muted"><small>We have sent verification code to your e-mail</small></div>
                            </div>
                            <div class="col-sm-6">
                                <div>
                                    <label style="width:100%">
                                        <span class="title">New Password</span>
                                        <input required type="password" class="form-control" style="margin-top:5px;" name="pass">
                                    </label>
                                </div>
                                <div>
                                    <label style="width:100%">
                                        <span class="title">Re-type Password</span>
                                        <input required type="password" class="form-control" style="margin-top:5px;" name="passver">
                                    </label>
                                </div>
                                <div class="invalid-feedback pid"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php $_csrf() ?>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    function __doMyForgot(r) {
        $("#loginCtn form").submit();
    }
    $("#loginCtn form").submit(e => {
        e.preventDefault();
        let f = $(e.target);
        let s = f.serialize();
        f.find("*").prop('disabled', true);
        $.post("/users/forgot1", s, d => {
            <?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
            grecaptcha.reset();
            <?php endif ?>
            f.find(".invalid-feedback").hide();
            f.find(".is-invalid").removeClass("is-invalid");
            f.find("*").prop('disabled', false);
            $("#verifyOTP").xmodal(m => {
                m.find(".text-muted small").text(d.method == "email" ? `Type the code we've just sent to your email at ${d.recipient}.` : (d.method == "phone" ? `Type the code we've just sent to your phone at ${d.recipient}.` : "Type the code on your authenticator app."));
                m.on('shown.bs.modal', () => {
                    m.find("input[name=code]")[0].select();
                });
                m.find("form").submit(e => {
                    e.preventDefault();
                    let f = $(e.target);
                    f.find(".invalid-feedback").hide();
                    f.find(".is-invalid").removeClass("is-invalid");
                    if (f.find("input[name=pass]").val() != f.find("input[name=passver]").val()) {
                        f.find("input[name=pass],input[name=passver]").addClass("is-invalid")[0].select();
                        f.find(".pid").text("Re-typed password is not same").show();
                    } else {
                        let s = f.serialize() + `&session=${d.hash}`;
                        f.find("*").prop('disabled', true);
                        $.post('/users/forgot2', s, d => {
                            f.find("*").prop('disabled', false);
                            if (d) {
                                m.modal('hide');
                                showMessage("Password was changed", "success");
                                setTimeout(() => {
                                    location.replace('/');
                                }, 1000);
                            } else {
                                f.find("input[name=code]").addClass('is-invalid')[0].select();
                                f.find(".vcw").text("Verification code is wrong.").show();
                            }
                        }).fail(r => {
                            f.find("*").prop('disabled', false);
                            showMessage(`An error occured. ${r.statusText}.`, "danger");
                        });
                    }
                });
            });
        }).fail(r => {
            <?php if (PuzzleUserConfig::recaptchaEnabled()) : ?>
            grecaptcha.reset();
            <?php endif ?>
            f.find("*").prop('disabled', false);
            f.find("input[name=user]").addClass("is-invalid")[0].select();
            f.find(".invalid-feedback").text(r.responseJSON.reason).show();
        });
    });
</script>
<?php echo Minifier::outJSMin() ?>