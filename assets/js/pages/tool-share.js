(function () {
  "use strict";

  function notify(message) {
    if (typeof window.showNotification === "function") {
      window.showNotification(message);
      return;
    }

    console.log(message);
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

  function fallbackCopy(url) {
    var textarea = document.createElement("textarea");
    textarea.value = url;
    textarea.setAttribute("readonly", "");
    textarea.style.position = "fixed";
    textarea.style.left = "-9999px";
    document.body.appendChild(textarea);
    textarea.select();

    var copied = false;
    try {
      copied = document.execCommand("copy");
    } catch (error) {
      copied = false;
    }

    document.body.removeChild(textarea);

    if (copied) {
      notify("Link copied");
    } else {
      notify("Unable to copy link");
    }
  }

  function copyUrl(url) {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(url).then(function () {
        notify("Link copied");
      }).catch(function () {
        fallbackCopy(url);
      });
      return;
    }

    fallbackCopy(url);
  }

  function shareUrl(url, title) {
    if (navigator.share) {
      navigator.share({
        title: title,
        text: "View this chart on 360MiQ",
        url: url
      }).catch(function (error) {
        if (error && error.name === "AbortError") {
          return;
        }

        copyUrl(url);
      });
      return;
    }

    copyUrl(url);
  }

  function bindButton(id, urlFactory, title, useNativeShare) {
    var button = document.getElementById(id);
    if (!button) {
      return;
    }

    button.addEventListener("click", function () {
      var url = urlFactory();
      if (useNativeShare) {
        shareUrl(url, title);
      } else {
        copyUrl(url);
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
