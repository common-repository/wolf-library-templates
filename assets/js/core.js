!function (t) {
    let e, n, a, o, i = t(document), r = !1, c = !1;
    const l = function (t) {
        let e, n, a = window.location.search.substring(1).split("&");
        for (n = 0; n < a.length; n++) if ((e = a[n].split("="))[0] === t) return void 0 === e[1] || decodeURIComponent(e[1])
    }, s = Swal.mixin({
        position: "bottom-end",
        showConfirmButton: !1,
        allowOutsideClick: !1,
        allowEscapeKey: !1,
        toast: !0
    }), u = function (e) {
        "object" != typeof e || null === e || e.success ? (t("#wolf-demo-popup").html(e), s.fire({
            type: "info",
            html: t("#wolf-demo-popup .wolf-notification-title").html()
        }), s.showLoading()) : d(e.data.message)
    }, d = function (t = null, e = null, n = null, a = null) {
        let o = advanced_import_object.text.failedImport.text;
        o += t || e || n || a ? "<br/>" + advanced_import_object.text.failedImport.code : "", o += t || "", o += e || "", o += n || "", o += a || "", s.fire({
            type: "error",
            html: o
        }), f()
    }, p = function (e = !1) {
        s.fire({type: "info", html: t(".wolf-notification-title").html()}), s.showLoading(), r = !0, m(e)
    }, f = function () {
        r = !1, _()
    }, m = function (t) {
        return !!t && (!c && ((c = t).append('<span class="wolf-update dashicons dashicons-update"></span>'), c.attr("disabled", !0), void c.closest(".wolf-item").addClass("wolf-action-importing")))
    }, _ = function () {
        if (!c) return !1;
        c.children(".wolf-update").remove(), c.attr("disabled", !1), c.closest(".wolf-item").removeClass("wolf-action-importing"), c = !1
    };

    function h() {
        return t.ajax({
            type: "POST",
            url: advanced_import_object.ajaxurl,
            data: {
                action: "content_screen",
                _wpnonce: e.val(),
                _wp_http_referer: n.val(),
                template_url: o,
                template_type: a
            }
        }).done(function (e) {
            if ("object" != typeof e || null === e || e.success) {
                u(e), (new function () {
                    let e, n = 0, i = "", r = "";

                    function c(n) {
                        "object" == typeof n && void 0 !== n.message ? (e.find("span").text(n.message), void 0 !== n.url ? n.hash === r ? (e.find("span").text(advanced_import_object.text.failed), l()) : (r = n.hash, t.ajax({
                            type: "POST",
                            url: n.url,
                            data: n
                        }).done(c).fail(c)) : (n.done, l())) : "object" != typeof n || null === n || n.success ? (e.find("span").text(advanced_import_object.text.error), l()) : d(n.data.errorMessage ? n.data.errorMessage : n.data.message)
                    }

                    function l() {
                        e && (e.data("done-item") || (n++, e.attr("data-done-item", 1)), e.find(".spinner").remove());
                        let r = !1, s = t("tr.wolf-available-content");
                        s.each(function () {
                            let n = t(this);
                            "" === i || r ? (i = n.data("content"), e = n, function () {
                                if (i) {
                                    let n = e.find("input:checkbox");
                                    n.is(":checked") ? t.ajax({
                                        type: "POST",
                                        url: advanced_import_object.ajaxurl,
                                        data: {
                                            action: "import_content",
                                            wpnonce: advanced_import_object.wpnonce,
                                            content: i,
                                            template_url: o,
                                            template_type: a
                                        }
                                    }).done(c).fail(c) : (e.find("span").text(advanced_import_object.text.skip), setTimeout(l, 300))
                                }
                            }(), r = !1) : n.data("content") === i && (r = !0)
                        }), n >= s.length && complete()
                    }

                    return {
                        init: function () {
                            let e = t(".wolf-pages");
                            e.addClass("installing"), e.find("input").prop("disabled", !0), complete = function () {
                                return t.ajax({
                                    type: "POST",
                                    url: advanced_import_object.ajaxurl,
                                    data: {action: "complete_screen"}
                                }).done(function (e) {
                                    return t("#wolf-demo-popup").html(e), Swal.fire({
                                        title: "Success",
                                        html: t("#wolf-demo-popup .wolf-notification-title").html(),
                                        type: "success",
                                        allowOutsideClick: !1,
                                        showCancelButton: !0,
                                        confirmButtonColor: "#3085d6",
                                        cancelButtonColor: "#d33",
                                        confirmButtonText: advanced_import_object.text.successImport.confirmButtonText,
                                        cancelButtonText: advanced_import_object.text.successImport.cancelButtonText
                                    }).then(e => {
                                        e.value && window.open(t("#wolf-demo-popup .wolf-actions-buttons a").attr("href"), "_blank")
                                    }), f(), !1
                                }).fail(function (t, e, n) {
                                    console.log(t + " :: " + e + " :: " + n)
                                }), !1
                            }, l()
                        }
                    }
                }).init()
            } else d(e.data.errorMessage ? e.data.errorMessage : e.data.message)
        }).fail(function (t, e, n) {
            return d("", t, e, n), !1
        }), !1
    }

    function v(a) {
        return t.ajax({
            type: "POST",
            url: advanced_import_object.ajaxurl,
            data: {action: "plugin_screen", _wpnonce: e.val(), _wp_http_referer: n.val(), recommendedPlugins: a}
        }).done(function (a) {
            u(a), s.showLoading(), t("#wolf-demo-popup .wolf-plugins-wrap").find("li").each(function () {
                return function a(o) {
                    if ("wolf-no-recommended-plugins" === o.attr("id")) return h(), !1;
                    t.ajax({
                        type: "POST",
                        url: advanced_import_object.ajaxurl,
                        data: {
                            action: "install_plugin",
                            _wpnonce: e.val(),
                            _wp_http_referer: n.val(),
                            slug: o.data("slug"),
                            plugin: o.data("slug") + "/" + o.data("main_file")
                        }
                    }).done(function (t) {
                        if ("object" == typeof t && void 0 !== t.success) if (t.success) {
                            if (o.attr("data-completed", 1), !o.next("li").length) return h(), !1;
                            setTimeout(a(o.next("li")), 1e3)
                        } else d(t.data.errorMessage ? t.data.errorMessage : t.data.message); else setTimeout(a(o), 1e3)
                    }).fail(function (t, e, n) {
                        return d("", t, e, n), !1
                    })
                }(t(this)), !1
            })
        }).fail(function (t, e, n) {
            return d("", t, e, n), !1
        }), !1
    }

    i.ready(function () {
        i.on("submit", "#wolf-upload-zip-form", function (a) {
            if (a.preventDefault(), r) return !1;
            !function (a) {
                if (void 0 === window.FormData) return !0;
                let o = new FormData, i = a.find("#wolf-upload-zip-archive"), r = t("#wolf-empty-file");
                if (!i.val()) return r.show(), d(r.html()), !1;
                r.hide(), p();
                let c = i[0].files[0];
                e = a.find("input[name=_wpnonce]"), n = a.find("input[name=_wp_http_referer]"), o.append("wolf-upload-zip-archive", c), o.append("action", "advanced_import_ajax_setup"), o.append("_wpnonce", e.val()), o.append("_wp_http_referer", n.val()), i.val(""), t.ajax({
                    type: "POST",
                    url: advanced_import_object.ajaxurl,
                    data: o,
                    cache: !1,
                    contentType: !1,
                    processData: !1
                }).done(function (t) {
                    return "object" != typeof t && (t = JSON.parse(t)), t.success ? (h(), !1) : (d(t.data.message), !1)
                }).fail(function (t, e, n) {
                    return d("", t, e, n), !1
                })
            }(t(this))
        }), i.on("click", ".wolf-item .wolf-demo-import", function (i) {
            if (i.preventDefault(), r) return !1;
            let c = t(this), l = t(this).data("plugins"), s = "", u = advanced_import_object.text.confirmImport.html;
            l ? (l.forEach(function (t, e) {
                t.name && (s += " " + t.name, e < l.length - 1 && (s += ","))
            }), u = u.replace("ai_replace_plugins", s)) : u = u.replace("ai_replace_plugins", advanced_import_object.text.confirmImport.no_plugins), Swal.fire({
                title: advanced_import_object.text.confirmImport.title,
                html: u,
                width: "64rem",
                customClass: {content: "wolf-confirm-import-content"},
                allowOutsideClick: !1,
                showCancelButton: !0,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: advanced_import_object.text.confirmImport.confirmButtonText,
                cancelButtonText: advanced_import_object.text.confirmImport.cancelButtonText
            }).then(i => {
                i.value && (p(c), function (i, r) {
                    let c = i.closest(".wolf-item");
                    a = c.data("template_type"), o = c.data("template_url"), e = c.find("input[name=_wpnonce]"), n = c.find("input[name=_wp_http_referer]"), "array" === a ? v(r) : t.ajax({
                        type: "POST",
                        url: advanced_import_object.ajaxurl,
                        data: {
                            action: "demo_download_and_unzip",
                            _wpnonce: e.val(),
                            _wp_http_referer: n.val(),
                            demo_file: o,
                            demo_file_type: a
                        }
                    }).done(function (t) {
                        return t.success ? (v(r), !1) : (d("", jqXHR, textStatus, errorThrown), !1)
                    }).fail(function (t, e, n) {
                        return d("", t, e, n), !1
                    })
                }(c, l))
            })
        }), i.on("click", ".wolf-wp-reset", function (e) {
            e.preventDefault(), Swal.fire({
                title: advanced_import_object.text.confirmReset.title,
                text: advanced_import_object.text.confirmReset.text,
                type: "warning",
                allowOutsideClick: !1,
                showCancelButton: !0,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: advanced_import_object.text.confirmReset.confirmButtonText,
                cancelButtonText: advanced_import_object.text.confirmReset.cancelButtonText
            }).then(e => {
                e.value && (window.location.href = t(".wolf-wp-reset").attr("href"))
            })
        }), i.on("click", ".wolf-filter-tabs li", function (e) {
            if (e.preventDefault(), r) return !1;
            t(this).hasClass("wolf-form-file-import") ? (t(".wolf-filter-content").addClass("hidden"), t(".wolf-form").removeClass("hidden")) : (t(".wolf-form").addClass("hidden"), t(".wolf-filter-content").removeClass("hidden"))
        });
        let c, s, u = {};
        setTimeout(function () {
            let e = t(".wolf-filter-content-wrapper").isotope({
                itemSelector: ".wolf-item", filter: function () {
                    let e = t(this), n = !s || e.text().match(s), a = !c || e.is(c);
                    return n && a
                }
            });

            function n() {
                let n = e.isotope("getFilteredItemElements"), a = t(n);
                t(".wolf-filter-btn").each(function (e, n) {
                    let o = t(n), i = o.attr("data-filter");
                    if (!i) return;
                    let r = a.filter(i).length;
                    o.find(".wolf-count").text(r)
                })
            }

            e.imagesLoaded().progress(function () {
                e.isotope("layout")
            }), n(), t(".wolf-filter-group").on("click", ".wolf-filter-btn", function () {
                let a = t(this), o = a.parents(".wolf-filter-group").attr("data-filter-group");
                a.siblings().removeClass("wolf-filter-btn-active"), a.addClass("wolf-filter-btn-active"), u[o] = a.attr("data-filter"), c = function (t) {
                    let e = "";
                    for (let n in t) e += t[n];
                    return e
                }(u), setTimeout(function () {
                    e.isotope(), (a.hasClass("wolf-fp-filter") || a.hasClass("wolf-type-filter")) && n()
                }, 300)
            });
            let a = t(".wolf-search-filter").keyup(function (t, e) {
                let n;
                return e = e || 100, function () {
                    clearTimeout(n);
                    let a = arguments, o = this;
                    n = setTimeout(function () {
                        t.apply(o, a)
                    }, e)
                }
            }(function () {
                s = new RegExp(a.val(), "gi"), e.isotope(), n()
            }))
        }, 1), function () {
            let t = l("reset"), e = l("from");
            "true" === t && "wolf-reset-wp" === e && Swal.fire({
                title: advanced_import_object.text.resetSuccess.title,
                type: "success",
                allowOutsideClick: !1,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: advanced_import_object.text.resetSuccess.confirmButtonText
            })
        }()
    }), setTimeout(function () {
        let t = window.location.href;
        if (new RegExp("[?|&]reset=[0-9a-zA-Z_+-|.,;]*").test(t)) {
            let t = new URL(location);
            t.searchParams.delete("reset"), t.searchParams.delete("from"), history.replaceState(null, null, t)
        }
    }, 1500)
}(jQuery);