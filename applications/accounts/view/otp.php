<div class="modal fade" id="verifyOTP">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Two Factor Authentication</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div style="max-width:600px;margin:auto;">
                        <label style="width:100%">
                            <span class="title">Verification code</span>
                            <input required type="text" class="form-control" style="margin-top:5px;" name="code">
                        </label>
                        <div class="invalid-feedback"></div>
                        <div class="text-muted"><small>We have sent verification code to your e-mail</small></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php $_csrf()?>
                    <button type="submit" class="btn btn-primary">Verify</button>
                </div>
            </form>
        </div>
    </div>
</div>