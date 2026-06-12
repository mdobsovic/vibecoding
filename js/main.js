/* =========================================================
   Michal Dobsovic - osobna stranka
   Zakladna interaktivita: mobilne menu, rok v paticke.
   ========================================================= */

(function () {
  "use strict";

  // Mobilna navigacia - otvaranie / zatvaranie
  var toggle = document.getElementById("navToggle");
  var menu = document.getElementById("navMenu");

  if (toggle && menu) {
    toggle.addEventListener("click", function () {
      var isOpen = menu.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");
      toggle.setAttribute("aria-label", isOpen ? "Zatvorit menu" : "Otvorit menu");
    });

    // Po kliknuti na polozku menu ho na mobile zavrieme
    menu.addEventListener("click", function (e) {
      if (e.target.tagName === "A") {
        menu.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  // Aktualny rok v paticke
  var yearEl = document.getElementById("year");
  if (yearEl) {
    yearEl.textContent = new Date().getFullYear();
  }
})();
