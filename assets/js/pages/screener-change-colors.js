(function () {
  function applyScreenerChangeColors() {
    document.querySelectorAll('#screener_grid font[color="green"]').forEach(function (element) {
      element.style.color = "green";
      element.style.fontWeight = "550";
    });

    document.querySelectorAll('#screener_grid font[color="red"]').forEach(function (element) {
      element.style.color = "red";
      element.style.fontWeight = "550";
    });

    document.querySelectorAll('#screener_grid font[color="grey"]').forEach(function (element) {
      element.style.color = "darkgrey";
      element.style.fontWeight = "550";
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    applyScreenerChangeColors();

    if (window.jQuery) {
      jQuery(document).on("draw.dt xhr.dt", "#screener_grid", applyScreenerChangeColors);
    }
  });
})();
