<style>
    .usrop label.is-invalid {
        color: var(--danger)
    }
</style>
<div class="usrop">
    <div>
        <h2>Profile Fields</h2>
        <div>
            <label>
                <input type="checkbox" n="emailRequired" <?php if (PuzzleUserConfig::emailRequired()) h("checked") ?>>
                <span>E-mail is required</span>
            </label>
        </div>
        <div>
            <label>
                <input type="checkbox" n="phoneRequired" <?php if (PuzzleUserConfig::phoneRequired()) h("checked") ?>>
                <span>Phone number is required</span>
            </label>
        </div>
        <div>
            <label>
                <input type="checkbox" n="allowChangeLocale" <?php if (PuzzleUserConfig::allowChangeLocale()) h("checked") ?>>
                <span>Allow user to change Language</span>
                <div class="text-muted"><small>You may force the user to use the main language for this site.</small></div>
            </label>
        </div>
        <div>
            <label>
                <input type="checkbox" n="allowUserTFA" <?php if (PuzzleUserConfig::allowUserTFA()) h("checked") ?>>
                <span>Allow user to activate Two-Factor Authentication</span>
                <div class="text-muted"><small>TFA can cost some money by sending E-mail or Text Messages.</small></div>
            </label>
        </div>
        <br>
    </div>
    <div>
        <h2>Log-In Options</h2>
        <div>
            <label>
                <input type="checkbox" n="allowRegistration" <?php if (PuzzleUserConfig::allowRegistration()) h("checked") ?>>
                <div style="display:inline-block">
                    <span>Allow user registration</span>
                    <div class="text-muted"><small>Select default group</small></div>
                    <?php (new Application("users"))->loadView("group_dropdown", [PuzzleUserConfig::defaultRegistrationGroup()->id, "defaultRegistrationGroup"]) ?>
                </div>
            </label>
        </div>
        <div>
            <label>
                <input type="checkbox" n="shareSessionToSubdomain" <?php if (PuzzleUserConfig::shareSessionToSubdomain()) h("checked") ?>>
                <span>Share session to subdomain</span>
            </label>
        </div>
        <div>
            <label>
                <input type="checkbox" n="enableRememberMe" <?php if (PuzzleUserConfig::enableRememberMe()) h("checked") ?>>
                <span>Retain session on the same PC</span>
            </label>
        </div>
        <div>
            <label>
                <input type="checkbox" n="creationRequireActivation" <?php if (PuzzleUserConfig::creationRequireActivation()) h("checked") ?>>
                <span>Registration always require activation</span>
            </label>
        </div>
    </div><br>
    <div style="max-width:400px;">
        <h2>Google reCaptcha</h2>
        <div>
            <label>
                <input type="checkbox" n="recaptchaEnabled" <?php if (PuzzleUserConfig::recaptchaEnabled()) h("checked") ?>>
                <span>Enable G-reCaptcha</span>
            </label>
        </div>
        <div class="row">
            <div class="col-sm-6">
                <label>
                    <span>Sitekey</span>
                    <input type="text" class="form-control" n="recaptchaSitekey" value="<?php h(PuzzleUserConfig::recaptchaSitekey()) ?>">
                </label>
            </div>
            <div class="col-sm-6">
                <label>
                    <span>Secretkey</span>
                    <input type="text" class="form-control" n="recaptchaPrivatekey" value="<?php h(PuzzleUserConfig::recaptchaPrivatekey()) ?>">
                </label>
            </div>
        </div>
    </div>
</div>
<?php ob_start() ?>
<script>
    $(".usrop .defaultRegistrationGroup").attr("n", "defaultRegistrationGroup");
    $(".usrop").find("input,select").on("change", function() {
        let t = this;
        t.disabled = true;
        let v = t.value;
        if (t.type == "checkbox") v = t.checked ? 1 : 0;
        $.post("/users/cop", {
            _token: "<?php h(session_id()) ?>",
            v: v,
            n: t.getAttribute("n")
        }, d => {
            t.disabled = false;
        }).fail(function() {
            t.disabled = false;
            showMessage("An error occured", "danger");
            $(t).closest("label").addClass("is-invalid");
        })
    });
</script>
<?php echo Minifier::outJSMin() ?>