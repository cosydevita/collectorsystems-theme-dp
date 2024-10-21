(() => {
    "use strict";
    var e = {
            963: (e, t, o) => {
                Object.defineProperty(t, "__esModule", {
                    value: !0
                }), t.BootstrapLightboxBuilder = void 0;
                var n = o(936),
                    r = function() {
                        function e(e, t) {
                            this.parentSelector = e, this.config = t, this.currentActiveImage = this.config.data[0]
                        }
                        return Object.defineProperty(e.prototype, "logoUri", {
                            get: function() {
                                return this.config.logo ? this.config.logo : null
                            },
                            enumerable: !1,
                            configurable: !0
                        }), Object.defineProperty(e.prototype, "prevImageIndex", {
                            get: function() {
                                var e = this,
                                    t = this.config.data.findIndex((function(t) {
                                        return t === e.currentActiveImage
                                    }));
                                return t > 0 ? t - 1 : this.config.data.length - 1
                            },
                            enumerable: !1,
                            configurable: !0
                        }), Object.defineProperty(e.prototype, "nextImageIndex", {
                            get: function() {
                                var e = this,
                                    t = this.config.data.findIndex((function(t) {
                                        return t === e.currentActiveImage
                                    }));
                                return t < this.config.data.length - 1 ? t + 1 : 0
                            },
                            enumerable: !1,
                            configurable: !0
                        }), e.prototype.buildGallery = function() {
                            this.buildOverlay(), this.buildGalleryContainer(), this.setUpControls()
                        }, e.prototype.buildOverlay = function() {
                            this.galleryOverlay = document.createElement("div"), this.galleryOverlay.classList.add("bootstrap-lightbox-overlay"), this.galleryOverlay.id = this.config.name, this.parent = document.querySelector(this.parentSelector), this.parent && this.parent.appendChild(this.galleryOverlay)
                        }, e.prototype.buildGalleryContainer = function() {
                            this.galleryContainer = document.createElement("div"), this.galleryContainer.classList.add("bootstrap-lightbox"), this.galleryContainer.id = "galleryContainer", this.galleryContainer.innerHTML = '\n            <div class="bootstrap-lightbox__top">\n                <div class="bootstrap-lightbox__top__logo">\n                    ' + this.getLogo() + '\n                </div>\n                <div id="bootstrap-lightbox_img_container" style="cursor:move;padding: 0px;float: left;margin: 0px;position: relative" class="bootstrap-lightbox__top__image-container">\n                    <img id="demo" class="bootstrap-lightbox__top__image-container__img" src="' + this.currentActiveImage + '" alt="Bootstrap 5 Lightbox active image">\n                </div>\n                <div class="bootstrap-lightbox__top__controls">\n                    ' + this.getControls() + '\n                </div>\n            </div>\n            <div class="bootstrap-lightbox__bottom">\n                <div class="bootstrap-lightbox__bottom__zoom">\n                    <button type="button" id="decreaseZoomBtn" class="btn"><i class="bi bi-dash-circle"></i></button>\n                    <input type="range" id="zoomRange" class="form-range bootstrap-lightbox__bottom__zoom-range" value="0">\n                    <button type="button" id="increaseZoomBtn" class="btn"><i class="bi bi-plus-circle"></i></button>\n                </div>\n            </div>\n        ', this.galleryOverlay.appendChild(this.galleryContainer)
                        }, e.prototype.getLogo = function() {
                            return this.logoUri ? '<img id="demo2" class="bootstrap-lightbox__top__logo__img" src="' + this.logoUri + '" alt="Bootstrap 5 Lightbox logo">' : ""
                        }, e.prototype.getControls = function() {
                            return '\n            <button type="button" id="prevImageBtn" class="btn"><i class="bi bi-chevron-left"></i></button>\n            <button type="button" id="nextImageBtn" class="btn"><i class="bi bi-chevron-right"></i></button>\n            <button type="button" id="closeGalleryBtn" class="btn"><i class="bi bi-x-lg"></i></button>\n        '
                        }, e.prototype.setUpControls = function() {
                            var e = this;
                            n.DomHelper.addEventListener("#prevImageBtn", "click", (function() {
                                return e.setImageAsActive(e.config.data[e.prevImageIndex])
                            })), n.DomHelper.addEventListener("#nextImageBtn", "click", (function() {
                                return e.setImageAsActive(e.config.data[e.nextImageIndex])
                            })), n.DomHelper.addEventListener("#closeGalleryBtn", "click", (function() {
                                return e.closeGallery()
                            })), n.DomHelper.addEventListener("#zoomRange", "input", (function() {
                                return e.zoomImage()
                            })), n.DomHelper.addEventListener("#decreaseZoomBtn", "click", (function() {
                                return e.decreaseZoom()
                            })), n.DomHelper.addEventListener("#decreaseZoomBtn", "mousedown", (function() {
                                return e.onRangeButtonMouseDown(!1)
                            })), n.DomHelper.addEventListener("#decreaseZoomBtn", "mouseup", (function() {
                                return e.onRangeButtonMouseUp()
                            })), n.DomHelper.addEventListener("#increaseZoomBtn", "click", (function() {
                                return e.increaseZoom()
                            })), n.DomHelper.addEventListener("#increaseZoomBtn", "mousedown", (function() {
                                return e.onRangeButtonMouseDown(!0)
                            })), n.DomHelper.addEventListener("#increaseZoomBtn", "mouseup", (function() {
                                return e.onRangeButtonMouseUp()
                            }))
                        }, e.prototype.onRangeButtonMouseDown = function(e) {
                            var t = this;
                            this.rangeBtnMouseDownInterval = setInterval((function() {
                                e ? t.increaseZoom() : t.decreaseZoom()
                            }), 50)
                        }, e.prototype.onRangeButtonMouseUp = function() {
                            clearInterval(this.rangeBtnMouseDownInterval)
                        }, e.prototype.decreaseZoom = function() {
                            var e = document.querySelector("#zoomRange"),
                                t = parseInt(e.value),
                                o = t > 0 ? t - 1 : 0;
                            e.value = o, this.zoomImage()
                        }, e.prototype.increaseZoom = function() {
                            var e = document.querySelector("#zoomRange"),
                                t = parseInt(e.value),
                                o = t < 100 ? t + 1 : 100;
                            e.value = o, this.zoomImage()
                        }, e.prototype.setImageAsActive = function(e) {
                            var t = document.querySelector(".bootstrap-lightbox__top__image-container__img");
                            t && (t.src = e, this.currentActiveImage = e)
                        }, e.prototype.zoomImage = function() {
                            var e = document.querySelector("#zoomRange");
                            document.querySelector(".bootstrap-lightbox__top__image-container__img").style.transform = "scale(" + (1 + .02 * e.value) + ")"//, console.log(e.value)
                        }, e.prototype.closeGallery = function() {
                            this.galleryOverlay.remove()
                        }, e
                    }();
                t.BootstrapLightboxBuilder = r
            },
            936: (e, t, o) => {
                Object.defineProperty(t, "__esModule", {
                    value: !0
                }), t.DomHelper = void 0;
                var n = o(471),
                    r = function() {
                        function e() {}
                        return e.addEventListener = function(e, t, o) {
                            var r = document.querySelector(e);
                            r ? r.addEventListener(t, o) : n.LogService.error("Can't register event listener - selector " + e + " not found")
                        }, e
                    }();
                t.DomHelper = r
            },
            471: (e, t) => {
                Object.defineProperty(t, "__esModule", {
                    value: !0
                }), t.LogService = void 0;
                var o = function() {
                    function e() {}
                    return e.error = function(e) {
                        console.error("Bootstrap Lightbox error: " + e + ".")
                    }, e
                }();
                t.LogService = o
            }
        },
        t = {};

    function o(n) {
        var r = t[n];
        if (void 0 !== r) return r.exports;
        var i = t[n] = {
            exports: {}
        };
        return e[n](i, i.exports, o), i.exports
    }
    var n = {};
    (() => {
        var e = n;
        Object.defineProperty(e, "__esModule", {
            value: !0
        }), e.BootstrapLightbox = void 0;
        var t = o(963),
            r = o(471),
            i = function() {
                function e(e, t) {
                    this.clickSelector = e, this.config = t
                }
                return e.prototype.createGallery = function() {
                    this.addAnchorClickEventListener()
                }, e.prototype.addAnchorClickEventListener = function() {
                    var e = this,
                        t = document.querySelector(this.clickSelector);
                    t ? t.addEventListener("click", (function() {
                        e.openGallery()
                    })) : r.LogService.error("Couldn't find element " + this.clickSelector)
                }, e.prototype.openGallery = function() {
                    console.log("open gallery", this.config),
                    new t.BootstrapLightboxBuilder("body", this.config).buildGallery()
                }, e
            }();
        e.BootstrapLightbox = i
    })(), self.BootstrapLightboxModule = n
})();
