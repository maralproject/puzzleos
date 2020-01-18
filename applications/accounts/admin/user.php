<?php ob_start() ?>
<style>
    #usrmgmt .hoveractbtn button {
        padding: 10px;
    }

    #usrmgmt .hoveractbtn {
        opacity: 0;
        position: absolute;
        right: 0;
        height: 100%;
        top: 0;
        display: flex;
        align-items: center;
        background: white;
        box-shadow: -9px 1px 9px -4px white;
        transition: opacity .1s ease-in;
    }

    #usrmgmt th input,
    #usrmgmt td select {
        min-width: 150px;
    }

    #usrmgmt th input.form-control,
    #usrmgmt td input.form-control {
        display: none;
    }

    #usrmgmt th:hover .hoveractbtn,
    #usrmgmt td:hover .hoveractbtn {
        opacity: 1
    }

    #usrmgmt tr.active {
        background: #fff4b8;
    }

    .tmpl {
        display: none;
    }
</style>
<?php echo Minifier::outCSSMin() ?>

<div style="display:grid;grid-template-columns: auto max-content;">
    <div class="input-group" style="max-width:300px;">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fa fa-search"></i></span>
        </div>
        <input autocomplete="off" placeholder="Find user..." type="text" class="form-control usr_search">
    </div>
    <div>
        <button class="btn btn-primary addusrs">Add User</button>
        <button class="btn btn-danger delusrs" style="display:none;">Remove User(s)</button>
    </div>
</div>
<br>
<div id="usrmgmt">
    <div class="table-responsive">
        <table class="table align-items-center table-flush">
            <thead class="thead-light">
                <tr>
                    <th scope="col"></th>
                    <th scope="col">Fullname</th>
                    <th scope="col">Group</th>
                    <th scope="col">Status</th>
                    <th scope="col">Language</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">Phone Number</th>
                    <th scope="col"></th>
                </tr>
            </thead>
            <tbody>
                <tr class="tmpl">
                    <td>
                        <label style="margin:0"><input type="checkbox"></label>
                    </td>
                    <th scope="row" style="position:relative;" class="iname">
                        <div class="media align-items-center">
                            <div class="media-body">
                                <span class="mb-0 text-sm"></span>
                                <input type="text" class="form-control">
                            </div>
                        </div>
                        <div class="hoveractbtn">
                            <button class="btn btn-link"> <i class="fa fa-pencil"></i> </button>
                        </div>
                    </th>
                    <td class="igroup">
                        <?php (iApplication::run("users"))->loadView("group_dropdown", []) ?>
                    </td>
                    <td style="position:relative;" class="ienable">
                        <span class="badge badge-dot mr-4">
                            <div class="ens"><i class="bg-success"></i>Enabled</div>
                            <div class="dns"><i class="bg-danger"></i>Disabled</div>
                        </span>
                        <div class="hoveractbtn">
                            <button class="btn btn-link"> <i class="fa fa-redo"></i> </button>
                        </div>
                    </td>
                    <td class="ilang"><?php LangManager::dumpForm("lang_" . $u["id"], $u["lang"], false, false, false) ?></td>
                    <td style="position:relative;" class="imail">
                        <div>
                            <span></span>
                            <input type="text" class="form-control">
                        </div>
                        <div class="hoveractbtn">
                            <button class="btn btn-link"> <i class="fa fa-pencil"></i> </button>
                        </div>
                    </td>
                    <td style="position:relative;" class="iphone">
                        <div>
                            <span></span>
                            <input type="text" class="form-control">
                        </div>
                        <div class="hoveractbtn">
                            <button class="btn btn-link"> <i class="fa fa-pencil"></i> </button>
                        </div>
                    </td>
                    <td class="text-right">
                        <div class="dropdown">
                            <a class="btn btn-sm btn-icon-only" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                                <a class="dropdown-item cpass" href="#">Change Password</a>
                                <a class="dropdown-item delusr" href="#"><span style="color:var(--danger)">Delete</span></a>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="statuses">
                    <th colspan="8">
                        <div style="text-align:center;padding:15px;text-transform:uppercase;"></div>
                    </th>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer py-4">
        <nav>
            <ul class="pagination justify-content-end mb-0 ppp">
                <li class="page-item npb">
                    <a class="page-link" href="#">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
                <li class="page-item npb">
                    <a class="page-link" href="#">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>

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
                    <input type="hidden" name="_token" value="<?php h(session_id()) ?>">
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addUser">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add User</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div style="max-width:600px;">
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
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start() ?>
<script>
    (function() {
        let page, data, limit = 15,
            checked = {},
            search = $(".usr_search");
        let range = (s, e, t) => {
            let y = [];
            for (let i = s; i <= e; i += (t || 1)) {
                y.push(i);
            }
            return y;
        };
        let cpagination = (data, limit, current, adjacents) => {
            let result = [];
            if (data && limit) {
                result = range(1, Math.ceil(data / limit));
                if (current && adjacents) {
                    if ((adjacents = Math.floor(adjacents / 2) * 2 + 1) >= 1) {
                        let offset = Math.max(0, Math.min(result.length - adjacents, current - Math.ceil(adjacents / 2)));
                        result = result.slice(
                            offset,
                            offset + adjacents
                        );
                    }
                }
            }
            return result;
        };
        let drawPagination = len => {
            let a = $("#usrmgmt .ppp");
            a.find('> :not(.npb)').remove();
            let b = a.find(".npb").removeClass("disabled").last();
            cpagination(len, limit, page, 4).forEach(a => {
                $(`<li class="page-item ${a==page?"active":""}"><a class="page-link" href="#">${a}</a></li>`).insertBefore(b).click(() => {
                    page = a;
                    draw();
                });
            });
            if (page <= 1) {
                a.find('.npb:eq(0)').addClass("disabled")[0].onclick = null;
            } else {
                a.find('.npb:eq(0)')[0].onclick = () => {
                    page--;
                    draw();
                };
            }
            if (page >= Math.ceil(len / limit)) {
                a.find('.npb:eq(1)').addClass("disabled")[0].onclick = null;
            } else {
                a.find('.npb:eq(1)')[0].onclick = () => {
                    page++;
                    draw();
                };
            }
        };
        let draw = function() {
            let _d = data,
                g;
            if ((g = search.val().toUpperCase()) != "") {
                _d = [];
                data.forEach(a => {
                    if (a.fullname.toUpperCase().includes(g)) _d.push(a);
                    else if ((a.phone || "").toUpperCase().includes(g)) _d.push(a);
                    else if ((a.email || "").toUpperCase().includes(g)) _d.push(a);
                });
            }
            drawPagination(_d.length);
            $("#usrmgmt .uitem").remove();
            let tmpl = $("#usrmgmt .tmpl"),
                s = $("#usrmgmt .statuses");
            for (let i = (page - 1) * limit; i < ((page - 1) * limit + limit); i++) {
                let d = _d[i];
                if (!d) break;
                let j = tmpl.clone().removeClass("tmpl").toggleClass("active", checked[d.id] || !1);
                j.find("input[type=checkbox]").prop("checked", checked[d.id])[0].onchange = () => {
                    checked[d.id] = checked[d.id] ? false : true;
                    draw();
                };
                j.attr("uid", d.id).addClass("uitem");
                j.find(".iname span").text(d.fullname);
                j.find(".iname .hoveractbtn button").click(function() {
                    let a = $(this).hide();
                    let b = a.parent().prev();
                    let e = b.find('span').hide();
                    let c = b.find("input").removeClass("is-invalid").show().val(e.text());
                    c[0].select();
                    let f = () => {
                        c[0].disabled = true;
                        $.post('/users/cuname', {
                            _token: "<?php h(session_id()) ?>",
                            val: c.val(),
                            uid: d.id
                        }, d => {
                            c[0].disabled = false;
                            a.show();
                            e.show().text(c.val());
                            c.hide();
                        }).fail(() => {
                            c[0].disabled = false;
                            c[0].focus();
                            c.addClass("is-invalid");
                            showMessage("Cannot save user", "danger");
                        });
                    };
                    c.on('keydown', e => {
                        if (e.keyCode == 13) f();
                    });
                    c.blur(f);
                });
                j.find(".igroup select").val(d.group.id)[0].onchange = e => {
                    e.target.disabled = true;
                    $.post('/users/cugroup', {
                        _token: "<?php h(session_id()) ?>",
                        val: e.target.value,
                        uid: d.id
                    }, d => {
                        e.target.disabled = false;
                        $(e.target).addClass("is-valid");
                    }).fail(() => {
                        e.target.disabled = false;
                        $(e.target).addClass("is-invalid");
                        showMessage("Cannot save user", "danger");
                    });
                };
                j.find(".ienable .ens").toggle(d.enabled);
                j.find(".ienable .dns").toggle(!d.enabled);
                j.find(".ienable .hoveractbtn button").click(e => {
                    $.post('/users/cuenable', {
                        _token: "<?php h(session_id()) ?>",
                        uid: d.id
                    }, d => {
                        j.find(".ienable .ens").toggle(d);
                        j.find(".ienable .dns").toggle(!d);
                    }).fail(() => {
                        showMessage("Cannot save user", "danger");
                    });
                });
                j.find(".ilang select").val(d.lang)[0].onchange = e => {
                    e.target.disabled = true;
                    $.post('/users/culang', {
                        _token: "<?php h(session_id()) ?>",
                        val: e.target.value,
                        uid: d.id
                    }, d => {
                        e.target.disabled = false;
                        $(e.target).addClass("is-valid");
                    }).fail(() => {
                        e.target.disabled = false;
                        $(e.target).addClass("is-invalid");
                        showMessage("Cannot save user", "danger");
                    })
                };
                j.find(".imail span").text(d.email || "-");
                j.find(".imail .hoveractbtn button").click(function() {
                    let a = $(this).hide();
                    let b = a.parent().prev();
                    let e = b.find('span').hide();
                    let c = b.find("input").removeClass("is-invalid").show().val(e.text());
                    c[0].select();
                    let f = () => {
                        c[0].disabled = true;
                        $.post('/users/cumail', {
                            _token: "<?php h(session_id()) ?>",
                            val: c.val(),
                            uid: d.id
                        }, d => {
                            c[0].disabled = false;
                            a.show();
                            e.show().text(c.val() || "-");
                            c.hide();
                        }).fail(() => {
                            c[0].disabled = false;
                            c[0].focus();
                            c.addClass("is-invalid");
                            showMessage("Cannot save user", "danger");
                        });
                    };
                    c.on('keydown', e => {
                        if (e.keyCode == 13) f();
                    });
                    c.blur(f);
                });
                j.find(".iphone span").text(d.phone || "-");
                j.find(".iphone .hoveractbtn button").click(function() {
                    let a = $(this).hide();
                    let b = a.parent().prev();
                    let e = b.find('span').hide();
                    let c = b.find("input").removeClass("is-invalid").show().val(e.text());
                    c[0].select();
                    let f = () => {
                        c[0].disabled = true;
                        $.post('/users/cuphone', {
                            _token: "<?php h(session_id()) ?>",
                            val: c.val(),
                            uid: d.id
                        }, d => {
                            c[0].disabled = false;
                            a.show();
                            e.show().text(d || "-");
                            c.hide();
                        }).fail(() => {
                            c[0].disabled = false;
                            c[0].focus();
                            c.addClass("is-invalid");
                            showMessage("Cannot save user", "danger");
                        });
                    };
                    c.on('keydown', e => {
                        if (e.keyCode == 13) f();
                    });
                    c.blur(f);
                });
                j.find(".cpass").click(e => {
                    $("#changePassModal").xmodal(m => {
                        let a = m.find("input[name=passnew]");
                        let b = m.find("input[name=passver]");
                        m.on('shown.bs.modal', () => {
                            a[0].select();
                        });
                        m.find("form").submit(e => {
                            e.preventDefault();
                            let f = $(e.target);
                            let s = f.serialize() + `&uid=${d.id}`;
                            if (a.val() != b.val()) {
                                a.add(b).addClass('is-invalid');
                                m.find(".nn").show();
                            } else {
                                f.find("*").prop('disabled', true);
                                $.post("/users/cupass", s, d => {
                                    if (d) {
                                        m.modal('hide');
                                        showMessage("Password has been changed", "success");
                                    } else {
                                        f.find("*").prop('disabled', false);
                                        showMessage("Failed to change password", "danger");
                                    }
                                }).fail(() => {
                                    f.find("*").prop('disabled', false);
                                    showMessage("Failed to change password", "danger");
                                });
                            }
                        });
                    });
                });
                j.find('.delusr').click(() => {
                    if (confirm("Delete this user?")) {
                        j.slideUp();
                        $.post('/users/cudelusr', {
                            _token: "<?php h(session_id()) ?>",
                            uid: d.id
                        }, d => {
                            fetchPage();
                            showMessage("User is deleted", "success");
                        }).fail(() => {
                            j.slideDown();
                            showMessage("Cannot delete user", "danger");
                        });
                    }
                }).toggle(d.id != <?php h(PuzzleUser::active()->id) ?>);
                j.insertBefore(s);
            }
            $(".delusrs").hide();
            $(".addusrs").show();
            for (const k in checked) {
                if (checked.hasOwnProperty(k)) {
                    if (checked[k]) {
                        $(".delusrs").show();
                        $(".addusrs").hide();
                        break;
                    }
                }
            }
        };
        let fetchPage = a => {
            $("#usrmgmt tbody tr:not(.tmpl)").hide().filter(".statuses").show().find("div").text("Loading users...").css('color', "");
            $.post('/users/gulist', {
                _token: "<?php h(session_id()) ?>",
            }, d => {
                data = d;
                $("#usrmgmt .statuses").hide();
                checked = {};
                page = 1;
                draw();
                if (typeof a == "function") a();
            }).fail(() => {
                $("#usrmgmt .statuses div").text("An error occured").css('color', "var(--danger)");
            });
        };
        $('.addusrs').click(() => {
            $("#addUser").xmodal(m => {
                m.on("shown.bs.modal", () => {
                    m.find("input:visible:first").focus();
                });
                m.find("form").submit(e => {
                    e.preventDefault();
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
                        let s = f.serialize() + "&_token=<?php h(session_id()) ?>";
                        f.find("*").prop("disabled", true);
                        $.post("/users/cuadd", s, d => {
                            m.modal("hide");
                            fetchPage(() => {
                                page = Math.ceil(data.length / limit);
                                draw();
                            });
                        }).fail(e => {
                            f.find("*").prop("disabled", false);
                            $(`<div class="invalid-feedback">Either phone or e-mail address has been used.</div>`).appendTo(".error-place").show();
                        });
                    } else {
                        a[0].select();
                    }
                });
            });
        });
        $(".delusrs").click(() => {
            if (confirm("Delete theese users?")) {
                let us = [];
                for (const k in checked) {
                    if (checked.hasOwnProperty(k)) {
                        if (checked[k]) us.push(Number(k));
                    }
                }
                $("#usrmgmt tbody tr:not(.tmpl)").hide().filter(".statuses").show().find("div").text("Deleting users...").css('color', "");
                $.post("/users/cudels", {
                    _token: "<?php h(session_id()) ?>",
                    usrs: JSON.stringify(us)
                }, d => {
                    fetchPage();
                }).fail(e => {
                    $("#usrmgmt .statuses div").text("An error occured").css('color', "var(--danger)");
                });
            }
        });
        search.on("input", () => {
            page = 1;
            draw();
        });
        fetchPage();
    }());
</script>
<?php echo Minifier::outJSMin() ?>