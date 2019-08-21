<div style="max-width:600px;">
    <h2>My Profile</h2><br>
    <form action="/users/profile" method="post" class="profileFrm">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
            </div>
            <input maxlength="50" name="name" required type="text" autofocus class="form-control" placeholder="Full Name" value="<?php h(PuzzleUser::active()->fullname) ?>">
        </div><br>
        <?php if (PuzzleUserConfig::emailRequired()) : ?>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-envelope-o"></i></span>
            </div>
            <input name="email" type="email" class="form-control" placeholder="E-mail address" value="<?php h(PuzzleUser::active()->email) ?>">
        </div><br>
        <?php endif ?>
        <?php if (PuzzleUserConfig::phoneRequired()) : ?>
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fa fa-phone"></i></span>
            </div>
            <input name="phone" pattern="^[0-9\+]{8,15}$" type="text" class="form-control" placeholder="Phone Number" value="<?php h(PuzzleUser::active()->phone) ?>">
        </div><br>
        <?php endif ?>
        <?php if (PuzzleUserConfig::allowChangeLocale()) : ?>
        <?php LangManager::dumpForm("lang", PuzzleUser::active()->lang, false, false, true) ?><br>
        <?php endif ?>
        <?php if (PuzzleUserConfig::allowUserTFA()) : ?>
        <div>
            <label>
                <input type="checkbox" name="tfa" <?php if (PuzzleUser::active()->tfa) echo "checked" ?>>
                <span>Enable Two-Factor Authentication</span>
            </label>
        </div><br>
        <?php endif ?>
        <div>
            <?php $_csrf() ?>
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-secondary cpassbtn">Change Password</button>
        </div>
    </form>
</div>

<?php include "otp.php" ?>

<div class="modal fade" id="changePassModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Change Password</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div style="max-width:600px;">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-key"></i></span>
                            </div>
                            <input name="passold" required autofocus type="password" class="form-control" placeholder="Old Password">
                        </div>
                        <div class="invalid-feedback mm">Old password is different.</div>
                        <br>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-key"></i></span>
                            </div>
                            <input name="passnew" required type="password" class="form-control" placeholder="New Password">
                        </div><br>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-key"></i></span>
                            </div>
                            <input name="passver" required type="password" class="form-control" placeholder="Re-type New Password">
                        </div>
                        <div class="invalid-feedback nn">Password is different. Please re-type your new password.</div>
                        <br>
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
    (function() {
        let oe = $("input[name=email]").val();
        let op = $("input[name=phone]").val();

        $(".cpassbtn").click(function() {
            $("#changePassModal").xmodal(m => {
                m.on("shown.bs.modal", () => {
                    m.find("input:visible:first").focus();
                }).on("hide.bs.modal", () => {
                    if (location.hash == "#changepass") history.back();
                });
                m.find("form").submit(e => {
                    e.preventDefault();
                    let f = $(e.target);
                    f.find(".is-invalid").removeClass('is-invalid');
                    f.find(".invalid-feedback").hide();
                    if (f.find("input[name=passnew]").val() != f.find("input[name=passver]").val()) {
                        f.find("input[name=passver],input[name=passnew]").addClass("is-invalid")[0].select;
                        f.find(".nn").show();
                    } else {
                        let s = f.serialize();
                        f.find("*").prop("disabled", true);
                        $.post("/users/cpass", s, d => {
                            if (d) {
                                m.modal('hide');
                                showMessage("Password changed", "success");
                            } else {
                                f.find("*").prop("disabled", false);
                                f.find("input[name=passold]").addClass("is-invalid")[0].select();
                                f.find(".mm").show();
                            }
                        }).fail(function() {
                            f.find("*").prop("disabled", false);
                            showMessage("Failed to change password", "danger");
                        });
                    }
                })
            });
        });

        $(".profileFrm").submit(function(e) {
            e.preventDefault();
            let t = $(this);
            let f = t.serialize();
            t.find('*').prop("disabled", true).find(".is-invalid").removeClass('is-invalid');
            $.post("/users/profile", f, d => {
                if (d.saved) {
                    showMessage("Profile was updated", "success");
                }
                if (d.challenge_needed) {
                    new Promise((a, r) => {
                        if (d.tfa_challenge) {
                            $("input[name=tfa]")[0].checked = false;
                            $("#verifyOTP").xmodal(m => {
                                m.on('shown.bs.modal', function() {
                                    $(this).find("input").focus();
                                }).on('hide.bs.modal', () => {
                                    a();
                                });
                                m.find(".modal-title").text("Can you receive the code?");
                                let l = d.tfa_challenge.method == "email" ? `Type the code we've just sent to your email at ${d.tfa_challenge.recipient}.` : `Type the code we've just sent to your phone at ${d.tfa_challenge.recipient}.`;
                                m.find("small").text(l);
                                m.find("button[type=submit]").text("Activate TFA");
                                m.find("form").submit(e => {
                                    e.preventDefault();
                                    let f = $(e.target).serialize() + `&session=${d.tfa_challenge.hash}`;
                                    $(e.target).find("*").prop("disabled", true);
                                    $.post("/users/verify", f, r => {
                                        if (r) {
                                            $("input[name=tfa]")[0].checked = true;
                                            showMessage("Two-Factor Authentication enabled", "success");
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
                            if (d.tfa_challenge === false) {
                                alert("Cannot send code to activate Two-Factor Authentication!");
                            }
                            a();
                        }
                    }).then(() => {
                        return new Promise((a, r) => {
                            if (d.sms_challenge) {
                                $("input[name=phone]").val(op);
                                $("#verifyOTP").xmodal(m => {
                                    m.on('shown.bs.modal', function() {
                                        $(this).find("input").focus();
                                    }).on('hide.bs.modal', () => {
                                        a();
                                    });
                                    m.find(".modal-title").text("Verify your Phone");
                                    m.find("small").text(`Type the code we've just sent to your phone at ${d.sms_challenge.recipient}.`);
                                    m.find("form").submit(e => {
                                        e.preventDefault();
                                        let f = $(e.target).serialize() + `&session=${d.sms_challenge.hash}`;
                                        $(e.target).find("*").prop("disabled", true);
                                        $.post("/users/verify", f, r => {
                                            if (r) {
                                                $("input[name=phone]").val(d.sms_challenge.recipient);
                                                showMessage("Phone number verified", "success");
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
                                if (d.sms_challenge === false) {
                                    $("input[name=phone]").addClass("is-invalid");
                                    alert("Cannot send code to your new phone number!");
                                }
                                a();
                            }
                        });
                    }).then(r => {
                        if (d.mail_challenge) {
                            $("input[name=email]").val(oe);
                            $("#verifyOTP").xmodal(m => {
                                m.on('shown.bs.modal', function() {
                                    $(this).find("input").focus();
                                }).on('hide.bs.modal', () => {
                                    // a();
                                });
                                m.find(".modal-title").text("Verify your Email");
                                m.find("small").text(`Type the code we've just sent to your email at ${d.mail_challenge.recipient}.`);
                                m.find("form").submit(e => {
                                    e.preventDefault();
                                    let f = $(e.target).serialize() + `&session=${d.mail_challenge.hash}`;
                                    $(e.target).find("*").prop("disabled", true);
                                    $.post("/users/verify", f, r => {
                                        if (r) {
                                            $("input[name=email]").val(d.mail_challenge.recipient);
                                            showMessage("E-mail address verified", "success");
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
                            if (d.mail_challenge === false) {
                                $("input[name=email]").addClass("is-invalid");
                                alert("Cannot send code to your new e-mail address!");
                            }
                        }
                    });
                }
            }).fail(e => {
                showMessage(e.responseJSON.reason, "danger");
            }).always(() => {
                t.find('*').prop("disabled", false);
            });
        });

        $(function() {
            let a = () => {
                if (location.hash == "#changepass") $(".cpassbtn").click();
            }
            window.onhashchange = a;
            a();
        });
    }());
</script>
<?php echo Minifier::outJSMin() ?>