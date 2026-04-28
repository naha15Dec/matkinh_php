document.addEventListener("DOMContentLoaded", function () {
  const overlay = document.getElementById("luxConfirmOverlay");
  const title = document.getElementById("luxConfirmTitle");
  const message = document.getElementById("luxConfirmMessage");
  const okBtn = document.getElementById("luxConfirmOk");
  const cancelBtn = document.getElementById("luxConfirmCancel");
  const iconBox = document.getElementById("luxConfirmIcon");

  if (!overlay || !title || !message || !okBtn || !cancelBtn) return;

  document.querySelectorAll("[data-confirm-url]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      const url = this.dataset.confirmUrl || "#";
      const confirmTitle = this.dataset.confirmTitle || "Xác nhận thao tác";
      const confirmMessage =
        this.dataset.confirmMessage ||
        "Bạn có chắc muốn thực hiện thao tác này không?";
      const confirmText = this.dataset.confirmText || "Xác nhận";
      const icon = this.dataset.confirmIcon || "far fa-trash-alt";
      const type = this.dataset.confirmType || "danger";

      title.textContent = confirmTitle;
      message.innerHTML = confirmMessage;
      okBtn.textContent = confirmText;
      okBtn.href = url;

      iconBox.innerHTML = `<i class="${icon}"></i>`;

      okBtn.classList.remove("success", "warning", "danger");
      iconBox.classList.remove("success", "warning", "danger");

      okBtn.classList.add(type);
      iconBox.classList.add(type);

      overlay.classList.add("show");
    });
  });

  cancelBtn.addEventListener("click", function () {
    overlay.classList.remove("show");
  });

  overlay.addEventListener("click", function (e) {
    if (e.target === overlay) {
      overlay.classList.remove("show");
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      overlay.classList.remove("show");
    }
  });
});
