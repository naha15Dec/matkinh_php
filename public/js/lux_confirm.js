document.addEventListener("DOMContentLoaded", function () {
  const overlay = document.getElementById("luxConfirmOverlay");
  const title = document.getElementById("luxConfirmTitle");
  const message = document.getElementById("luxConfirmMessage");
  const okBtn = document.getElementById("luxConfirmOk");
  const cancelBtn = document.getElementById("luxConfirmCancel");
  const iconBox = document.getElementById("luxConfirmIcon");

  if (!overlay || !title || !message || !okBtn || !cancelBtn || !iconBox) {
    return;
  }

  let pendingForm = null;
  let pendingUrl = null;
  let isSubmitting = false;

  function openConfirm(trigger) {
    const confirmTitle = trigger.dataset.confirmTitle || "Xác nhận thao tác";

    const confirmMessage =
      trigger.dataset.confirmMessage ||
      "Bạn có chắc muốn thực hiện thao tác này không?";

    const confirmText =
      trigger.dataset.confirmOk || trigger.dataset.confirmText || "Xác nhận";

    const icon = trigger.dataset.confirmIcon || "far fa-trash-alt";
    const type = trigger.dataset.confirmType || "danger";

    title.textContent = confirmTitle;
    message.innerHTML = confirmMessage;
    okBtn.textContent = confirmText;

    iconBox.innerHTML = `<i class="${icon}"></i>`;

    okBtn.classList.remove("success", "warning", "danger");
    iconBox.classList.remove("success", "warning", "danger");

    okBtn.classList.add(type);
    iconBox.classList.add(type);

    overlay.classList.add("show");
  }

  function closeConfirm() {
    overlay.classList.remove("show");
    pendingForm = null;
    pendingUrl = null;
    isSubmitting = false;
  }

  // Dùng cho link hoặc button có data-confirm-url
  document.querySelectorAll("[data-confirm-url]").forEach(function (trigger) {
    trigger.addEventListener("click", function (e) {
      e.preventDefault();

      pendingUrl = this.dataset.confirmUrl || this.getAttribute("href") || "#";
      pendingForm = null;

      openConfirm(this);
    });
  });

  // Dùng cho link thường có data-confirm
  document
    .querySelectorAll("a[data-confirm]:not([data-confirm-url])")
    .forEach(function (trigger) {
      trigger.addEventListener("click", function (e) {
        e.preventDefault();

        pendingUrl = this.getAttribute("href") || "#";
        pendingForm = null;

        openConfirm(this);
      });
    });

  // Dùng cho form POST có button data-confirm
  document.querySelectorAll("form").forEach(function (form) {
    form.addEventListener("submit", function (e) {
      if (form.dataset.confirmed === "true") {
        form.dataset.confirmed = "false";
        return;
      }

      const submitter = e.submitter;

      if (!submitter || !submitter.hasAttribute("data-confirm")) {
        return;
      }

      e.preventDefault();

      pendingForm = form;
      pendingUrl = null;

      openConfirm(submitter);
    });
  });

  okBtn.addEventListener("click", function (e) {
    e.preventDefault();

    if (isSubmitting) {
      return;
    }

    isSubmitting = true;

    if (pendingForm) {
      pendingForm.dataset.confirmed = "true";
      pendingForm.submit();
      return;
    }

    if (pendingUrl) {
      window.location.href = pendingUrl;
      return;
    }

    closeConfirm();
  });

  cancelBtn.addEventListener("click", function () {
    closeConfirm();
  });

  overlay.addEventListener("click", function (e) {
    if (e.target === overlay) {
      closeConfirm();
    }
  });

  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && overlay.classList.contains("show")) {
      closeConfirm();
    }
  });
});
