document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | Dark Mode
    |--------------------------------------------------------------------------
    */

    const body = document.body;
    const darkKey = "filmworld_dark_mode";
    const isDark = localStorage.getItem(darkKey) === "true";

    if (isDark) {
        body.classList.add("filmworld-dark");
    }

    // Create toggle button
    const toggle = document.createElement("button");
    toggle.className = "filmworld-dark-toggle";
    toggle.setAttribute("aria-label", "تغییر تم");
    toggle.innerHTML = isDark ? "&#9728;" : "&#9790;";
    body.appendChild(toggle);

    toggle.addEventListener("click", function () {
        body.classList.toggle("filmworld-dark");
        const active = body.classList.contains("filmworld-dark");
        localStorage.setItem(darkKey, active);
        toggle.innerHTML = active ? "&#9728;" : "&#9790;";
    });

    /*
    |--------------------------------------------------------------------------
    | Favorite / Watchlist (AJAX)
    |--------------------------------------------------------------------------
    */

    body.addEventListener("click", function (e) {
        const favBtn = e.target.closest(".filmworld-fav-btn");
        if (!favBtn) return;

        e.preventDefault();
        e.stopPropagation();

        if (typeof filmworld_ajax === "undefined") return;

        const postId = favBtn.dataset.postId;
        if (!postId) return;

        const formData = new FormData();
        formData.append("action", "filmworld_toggle_favorite");
        formData.append("post_id", postId);
        formData.append("nonce", filmworld_ajax.nonce);

        fetch(filmworld_ajax.ajax_url, {
            method: "POST",
            body: formData,
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                favBtn.classList.toggle("active");
                favBtn.innerHTML = data.data.is_favorited ? "&#10084;" : "&#9825;";
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

    const modal = document.getElementById("filmworld-player-modal");
    const player = document.getElementById("filmworld-video-player");
    const closeBtn = document.getElementById("filmworld-player-close");

    if (modal && player) {
        document.querySelectorAll(".filmworld-stream-btn").forEach(function (btn) {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                player.src = this.dataset.video;
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

        if (closeBtn) {
            closeBtn.addEventListener("click", closePlayer);
        }

        modal.addEventListener("click", function (e) {
            if (e.target === modal) {
                closePlayer();
            }
        });

        document.addEventListener("keydown", function (e) {
            if (e.key === "Escape" && modal.classList.contains("active")) {
                closePlayer();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scroll Animations (Intersection Observer)
    |--------------------------------------------------------------------------
    */

    const observer = new IntersectionObserver(
        function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";
                }
            });
        },
        { threshold: 0.1 }
    );

    document.querySelectorAll(".filmworld-media-item").forEach(function (item) {
        item.style.opacity = "0";
        item.style.transform = "translateY(20px)";
        item.style.transition = "opacity 0.4s ease, transform 0.4s ease";
        observer.observe(item);
    });

});