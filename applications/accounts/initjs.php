<?php ob_start() ?>
<script>
    $(function() {
        if (!$.prototype['xmodal']) {
            $.prototype['xmodal'] = function(init) {
                return this.each(function() {
                    var x = $(this).clone();
                    x.appendTo("body").on("hidden.bs.modal", function() {
                        if ($(".modal.show").length > 0) $("body").addClass("modal-open");
                        $(this).remove();
                    });
                    if (typeof init == "function") init(x);
                    return x.modal("show");
                });
            }
        }
    })
</script>
<?php echo Minifier::outJSMin() ?>