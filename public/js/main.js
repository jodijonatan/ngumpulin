// public/js/main.js
document.addEventListener("DOMContentLoaded", function () {
  // delete confirm (buttons with data-confirm-delete)
  document.querySelectorAll("[data-confirm-delete]").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      if (!confirm("Are you sure to delete this item?")) e.preventDefault();
    });
  });

  // small helper: auto-dismiss alerts
  setTimeout(function () {
    document.querySelectorAll(".alert-dismissible").forEach(function (a) {
      a.classList.add("fade");
      a.classList.remove("show"); // bootstrap 5 uses show
    });
  }, 3500);
});
