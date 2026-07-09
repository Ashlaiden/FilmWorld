document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | Header Scroll Effect
    |--------------------------------------------------------------------------
    */
    var header = document.getElementById("fw-header");
    if (header) {
        window.addEventListener("scroll", function () {
            header.classList.toggle("scrolled", window.scrollY > 30);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Search Overlay
    |--------------------------------------------------------------------------
    */
    var searchToggle = document.getElementById("fw-search-toggle");
    var searchOverlay = document.getElementById("fw-search-overlay");
    var searchClose = document.getElementById("fw-search-close");
    var searchInput = document.getElementById("fw-search-input");

    if (searchToggle && searchOverlay) {
        searchToggle.addEventListener("click", function () {
            searchOverlay.classList.add("active");
            if (searchInput) setTimeout(function () { searchInput.focus(); }, 100);
        });

        if (searchClose) {
            searchClose.addEventListener("click", function () {
                searchOverlay.classList.remove("active");
            });
        }

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && searchOverlay.classList.contains("active")) {
                searchOverlay.classList.remove("active");
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Mobile Menu
    |--------------------------------------------------------------------------
    */
    var mobileToggle = document.getElementById("fw-mobile-toggle");
    var mobileNav = document.getElementById("fw-mobile-nav");

    if (mobileToggle && mobileNav) {
        mobileToggle.addEventListener("click", function () {
            mobileToggle.classList.toggle("active");
            mobileNav.classList.toggle("open");
            document.body.style.overflow = mobileNav.classList.contains("open") ? "hidden" : "";
        });
    }

    /*
    |--------------------------------------------------------------------------
    | User Dropdown
    |--------------------------------------------------------------------------
    */
    var userBtn = document.getElementById("fw-user-btn");
    var userDropdown = document.getElementById("fw-user-dropdown");

    if (userBtn && userDropdown) {
        userBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            userDropdown.parentElement.classList.toggle("open");
        });

        document.addEventListener("click", function () {
            userDropdown.parentElement.classList.remove("open");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Horizontal Slider Arrows
    |--------------------------------------------------------------------------
    */
    document.querySelectorAll(".fw-slider-wrapper").forEach(function (wrapper) {
        var slider = wrapper.querySelector(".fw-slider");
        var prevBtn = wrapper.querySelector(".fw-slider-arrow--prev");
        var nextBtn = wrapper.querySelector(".fw-slider-arrow--next");
        if (!slider) return;

        var scrollAmount = 400;

        if (prevBtn) {
            prevBtn.addEventListener("click", function () {
                slider.scrollBy({ left: -scrollAmount, behavior: "smooth" });
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener("click", function () {
                slider.scrollBy({ left: scrollAmount, behavior: "smooth" });
            });
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Favorite / Watchlist (AJAX)
    |--------------------------------------------------------------------------
    */
    document.body.addEventListener("click", function (e) {
        var favBtn = e.target.closest(".fw-card-fav");
        if (!favBtn) return;
        e.preventDefault();
        e.stopPropagation();
        if (typeof filmworld_ajax === "undefined") return;

        var postId = favBtn.dataset.postId;
        if (!postId) return;

        var formData = new FormData();
        formData.append("action", "filmworld_toggle_favorite");
        formData.append("post_id", postId);
        formData.append("nonce", filmworld_ajax.nonce);

        fetch(filmworld_ajax.ajax_url, { method: "POST", body: formData })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                favBtn.classList.toggle("active");
            } else {
                if (data.data && data.data.redirect) {
                    window.location.href = data.data.redirect;
                }
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Video Player Modal
    |--------------------------------------------------------------------------
    */
    var modal = document.getElementById("filmworld-player-modal");
    var player = document.getElementById("filmworld-video-player");
    var closeBtn = document.getElementById("filmworld-player-close");

    if (modal && player) {
        document.querySelectorAll(".filmworld-stream-btn, .fw-single-btn--play, .fw-hero-btn--primary").forEach(function (btn) {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                var videoUrl = this.dataset.video;
                if (!videoUrl) return;
                player.src = videoUrl;
                modal.classList.add("active");
                player.play();
            });
        });

        function closePlayer() {
            player.pause();
            player.removeAttribute("src");
            player.load();
            modal.classList.remove("active");
        }

        if (closeBtn) closeBtn.addEventListener("click", closePlayer);
        modal.addEventListener("click", function (e) { if (e.target === modal) closePlayer(); });
        document.addEventListener("keydown", function (e) { if (e.key === "Escape" && modal.classList.contains("active")) closePlayer(); });
    }

    /*
    |--------------------------------------------------------------------------
    | Card Hover Scroll Animation
    |--------------------------------------------------------------------------
    */
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.05 });

    document.querySelectorAll(".fw-card").forEach(function (card, i) {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        card.style.transition = "opacity 0.4s ease " + (i % 6) * 0.05 + "s, transform 0.4s ease " + (i % 6) * 0.05 + "s";
        observer.observe(card);
    });

});