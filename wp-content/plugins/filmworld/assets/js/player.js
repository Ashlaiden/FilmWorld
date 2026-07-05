document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("filmworld-player-modal");
    const player = document.getElementById("filmworld-video-player");
    const closeBtn = document.getElementById("filmworld-player-close");

    document.querySelectorAll(".filmworld-stream-btn").forEach(btn => {

        btn.addEventListener("click", function () {

            player.src = this.dataset.video;

            modal.style.display = "flex";

            player.play();

        });

    });

    closeBtn.addEventListener("click", function () {

        player.pause();
        player.removeAttribute("src");
        player.load();

        modal.style.display = "none";

    });

    modal.addEventListener("click", function (e) {

        if (e.target === modal) {

            player.pause();
            player.removeAttribute("src");
            player.load();

            modal.style.display = "none";

        }

    });

});