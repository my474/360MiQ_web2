(function () {
  "use strict";

  function notify(message, button) {
    if (button) {
      var originalText = button.getAttribute("data-original-html") || button.innerHTML;
      button.setAttribute("data-original-html", originalText);
      button.textContent = message;
      window.setTimeout(function () {
        button.innerHTML = originalText;
      }, 1800);
    }

    var notification = document.createElement("div");
    notification.className = "screener-share-notification";
    notification.textContent = message;
    document.body.appendChild(notification);
    window.setTimeout(function () {
      notification.classList.add("show");
    }, 10);
    window.setTimeout(function () {
      notification.classList.remove("show");
      window.setTimeout(function () {
        notification.remove();
      }, 500);
    }, 2200);
  }

  function getShareUrl() {
    return new URL(window.location.href).toString();
  }

  function fallbackCopy(url, button) {
    var selection = window.getSelection ? window.getSelection() : null;
    var ranges = [];
    var i;
    if (selection) {
      for (i = 0; i < selection.rangeCount; i += 1) {
        ranges.push(selection.getRangeAt(i));
      }
      selection.removeAllRanges();
    }

    var activeElement = document.activeElement;
    var textarea = document.createElement("textarea");
    textarea.value = String(url);
    textarea.setAttribute("readonly", "");
    textarea.setAttribute("aria-hidden", "true");
    textarea.style.position = "fixed";
    textarea.style.top = "0";
    textarea.style.left = "0";
    textarea.style.width = "1px";
    textarea.style.height = "1px";
    textarea.style.opacity = "0";
    document.body.appendChild(textarea);
    textarea.focus({ preventScroll: true });
    textarea.select();
    textarea.setSelectionRange(0, textarea.value.length);

    var copied = false;
    try {
      copied = document.execCommand("copy");
    } catch (error) {
      copied = false;
    }

    document.body.removeChild(textarea);

    if (selection) {
      selection.removeAllRanges();
      ranges.forEach(function (range) {
        selection.addRange(range);
      });
    }
    if (activeElement && typeof activeElement.focus === "function") {
      activeElement.focus({ preventScroll: true });
    }

    if (copied) {
      notify("Link copied", button);
    } else {
      window.prompt("Copy this screener link:", url);
      notify("Copy link shown", button);
    }
  }

  function copyUrl(url, button) {
    if (navigator.clipboard && typeof navigator.clipboard.writeText === "function") {
      navigator.clipboard.writeText(url).then(function () {
        notify("Link copied", button);
      }).catch(function () {
        fallbackCopy(url, button);
      });
      return;
    }

    fallbackCopy(url, button);
  }

  function shareUrl(url, button) {
    if (navigator.share) {
      navigator.share({
        title: "360MiQ Stock Screener",
        text: "View this stock screener on 360MiQ",
        url: url
      }).catch(function (error) {
        if (error && error.name === "AbortError") {
          return;
        }

        copyUrl(url, button);
      });
      return;
    }

    copyUrl(url, button);
  }

  function makeButton(id, label, iconClass, handler) {
    var button = document.createElement("button");
    button.id = id;
    button.className = "btn btn-outline-primary btn-sm";
    button.type = "button";
    button.title = label === "Share" ? "Share screener" : "Copy screener link";
    button.innerHTML = '<i class="' + iconClass + '"></i> ' + label;
    button.addEventListener("click", function () {
      handler(button);
    });
    return button;
  }

  function ensureShareActions() {
    var container = document.getElementById("searchresult");
    if (!container || container.querySelector(".screener-share-actions")) {
      return;
    }
    if (container.textContent.indexOf("Bookmark this search") === -1) {
      return;
    }

    var actions = document.createElement("div");
    actions.className = "screener-share-actions";
    actions.appendChild(makeButton("copyLinkScreener", "Copy Link", "fas fa-link", function (button) {
      copyUrl(getShareUrl(), button);
    }));
    actions.appendChild(makeButton("shareScreener", "Share", "fas fa-share-alt", function (button) {
      shareUrl(getShareUrl(), button);
    }));
    container.appendChild(actions);
  }

  function init() {
    var container = document.getElementById("searchresult");
    ensureShareActions();
    if (!container || !window.MutationObserver) {
      return;
    }

    var observer = new MutationObserver(ensureShareActions);
    observer.observe(container, { childList: true, subtree: true });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init, { once: true });
  } else {
    init();
  }
}());
