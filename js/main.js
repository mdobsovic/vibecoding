/* =========================================================
   Michal Dobsovic - osobna stranka
   Zakladna interaktivita: mobilne menu, rok v paticke,
   odoslanie kontaktneho formulara cez fetch (AJAX).
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

  // ---------- Kontaktny formular (odoslanie cez fetch) ----------
  var form = document.getElementById("contactForm");
  if (form) {
    var submitBtn = form.querySelector('button[type="submit"]');
    var btnDefaultText = submitBtn ? submitBtn.textContent : "";

    // Najde alebo vytvori prvok pre celkovu stavovu hlasku
    function getStatusBox() {
      var box = form.querySelector(".form-status");
      if (!box) {
        box = document.createElement("div");
        box.className = "form-status";
        box.setAttribute("role", "alert");
        form.insertBefore(box, form.firstChild);
      }
      return box;
    }

    function showStatus(type, message) {
      var box = getStatusBox();
      box.className = "form-status form-status--" + type;
      box.textContent = message;
    }

    // Zmaze vsetky chyby pri poliach aj celkovu hlasku
    function clearErrors() {
      form.querySelectorAll(".form-field--error").forEach(function (el) {
        el.classList.remove("form-field--error");
      });
      form.querySelectorAll(".form-field__error").forEach(function (el) {
        el.remove();
      });
      var box = form.querySelector(".form-status");
      if (box) {
        box.remove();
      }
    }

    // Zobrazi chybu pri konkretnom poli
    function showFieldError(name, message) {
      var input = form.querySelector('[name="' + name + '"]');
      if (!input) {
        return;
      }
      var field = input.closest(".form-field");
      if (!field) {
        return;
      }
      field.classList.add("form-field--error");
      var err = document.createElement("span");
      err.className = "form-field__error";
      err.textContent = message;
      field.appendChild(err);
    }

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      clearErrors();

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Odosielam…";
      }

      fetch(form.action, {
        method: "POST",
        body: new FormData(form),
        headers: {
          "X-Requested-With": "XMLHttpRequest",
          "Accept": "application/json"
        }
      })
        .then(function (res) {
          return res.json().then(function (data) {
            return { status: res.status, data: data };
          });
        })
        .then(function (result) {
          var data = result.data;

          if (data.ok) {
            form.reset();
            // Obnovenie CSRF tokenu pre pripadne dalsie odoslanie
            if (data.csrf) {
              var csrfInput = form.querySelector('[name="csrf"]');
              if (csrfInput) {
                csrfInput.value = data.csrf;
              }
            }
            // Obnovenie casovej znacky
            var tsInput = form.querySelector('[name="ts"]');
            if (tsInput) {
              tsInput.value = Math.floor(Date.now() / 1000);
            }
            showStatus("success", data.message);
          } else {
            if (data.errors) {
              Object.keys(data.errors).forEach(function (name) {
                showFieldError(name, data.errors[name]);
              });
            }
            showStatus("error", data.message || "Správu sa nepodarilo odoslať.");
          }
        })
        .catch(function () {
          showStatus("error", "Nastala chyba spojenia. Skúste to prosím znova.");
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = btnDefaultText;
          }
        });
    });
  }
})();
