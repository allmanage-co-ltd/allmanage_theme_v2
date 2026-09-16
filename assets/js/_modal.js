/*********************************************
  * イメージモーダル
  ********************************************/
$(function () {
    if ($(".item-image").length) {
        const $modal = $("#imgModal");
        const $img = $("#imgModal img");
        const scale = 2.5;

        let isDragging = false;
        let startX,
            startY,
            moveX = 0,
            moveY = 0;

        // モーダルを開く
        $(".item-image").on("click", function () {
            const src = $(this).find("img").attr("src");
            resetModal();
            $img.attr("src", src);
            $modal.fadeIn();
        });

        // モーダルをリセット
        function resetModal() {
            $img.removeClass("zoomed dragging");
            $img.css("transform", "translate(-50%, -50%)");
            moveX = moveY = 0;
        }

        // 画像クリックで拡大/縮小
        $img.on("click", function (e) {
            if (isDragging) return;

            if (!$img.hasClass("zoomed")) {
                // 拡大
                $img
                    .addClass("zoomed")
                    .css(
                        "transform",
                        `translate(calc(-50% + ${moveX}px), calc(-50% + ${moveY}px)) scale(${scale})`
                    );
            } else {
                // 縮小
                resetModal();
            }
        });

        // ドラッグ開始
        $img.on("mousedown", function (e) {
            if (!$img.hasClass("zoomed")) return;

            isDragging = true;
            $img.addClass("dragging");
            startX = e.pageX - moveX;
            startY = e.pageY - moveY;
            e.preventDefault();
        });

        // ドラッグ移動
        $(document).on("mousemove", function (e) {
            if (!isDragging) return;

            moveX = e.pageX - startX;
            moveY = e.pageY - startY;
            $img.css(
                "transform",
                `translate(calc(-50% + ${moveX}px), calc(-50% + ${moveY}px)) scale(${scale})`
            );
        });

        // ドラッグ終了
        $(document).on("mouseup", function () {
            if (isDragging) {
                isDragging = false;
                $img.removeClass("dragging");
            }
        });

        // 背景クリックで閉じる
        $modal.on("click", function (e) {
            if (e.target.id === "imgModal") {
                $modal.fadeOut();
            }
        });

        // ESCキーで閉じる
        $(document).on("keydown", function (e) {
            if (e.key === "Escape" && $modal.is(":visible")) {
                $modal.fadeOut();
            }
        });
    }

});
