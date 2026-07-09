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

    if (typeof window.showNotification === "function") {
      window.showNotification(message);
      return;
    }

    var notification = document.createElement("div");
    notification.className = "notification";
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

  function toAbsoluteUrl(url) {
    return new URL(url || window.location.href, window.location.origin).toString();
  }

  function getRaceShareUrl() {
    return toAbsoluteUrl(window.newRelativePathQueryTab1 || window.location.pathname + window.location.search);
  }

  function getComposerShareUrl() {
    return toAbsoluteUrl(window.newRelativePathQueryTab2 || window.location.pathname + window.location.search);
  }

  function fallbackCopy(url, button) {
    var selection = window.getSelection ? window.getSelection() : null;
    var ranges = [];
    if (selection) {
      for (var i = 0; i < selection.rangeCount; i += 1) {
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
      window.prompt("Copy this chart link:", url);
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

  function shareUrl(url, title, button) {
    if (navigator.share) {
      navigator.share({
        title: title,
        text: "View this chart on 360MiQ",
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

  function bindButton(id, urlFactory, title, useNativeShare) {
    var button = document.getElementById(id);
    if (!button) {
      return;
    }

    button.addEventListener("click", function () {
      var url = urlFactory();
      if (useNativeShare) {
        shareUrl(url, title, button);
      } else {
        copyUrl(url, button);
      }
    });
  }

  function setupSharing() {
    bindButton("copyLinkRace", getRaceShareUrl, "360MiQ Performance Race", false);
    bindButton("shareChartRace", getRaceShareUrl, "360MiQ Performance Race", true);
    bindButton("copyLinkComposer", getComposerShareUrl, "360MiQ Chart Composer", false);
    bindButton("shareChartComposer", getComposerShareUrl, "360MiQ Chart Composer", true);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", setupSharing, { once: true });
  } else {
    setupSharing();
  }
}());
