(function () {
    var grid = document.getElementById("blogGrid");
    if (!grid) {
        return;
    }

    var minGridColumns = 3;
    var syncing = false;

    function getPostParts(item) {
        return {
            container: item.querySelector('[id^="featuredpostcontainer"]'),
            title: item.querySelector('[id^="featuredposttitle"]')
        };
    }

    function hasPostContent(item) {
        var parts = getPostParts(item);
        return Boolean(
            (parts.container && parts.container.innerHTML.trim() !== "") ||
            (parts.title && parts.title.innerHTML.trim() !== "")
        );
    }

    function movePostContent(source, target) {
        var sourceParts = getPostParts(source);
        var targetParts = getPostParts(target);

        if (!sourceParts.container || !sourceParts.title || !targetParts.container || !targetParts.title) {
            return;
        }

        while (sourceParts.container.firstChild) {
            targetParts.container.appendChild(sourceParts.container.firstChild);
        }
        while (sourceParts.title.firstChild) {
            targetParts.title.appendChild(sourceParts.title.firstChild);
        }
        if (targetParts.title.style.cssText !== sourceParts.title.style.cssText) {
            targetParts.title.style.cssText = sourceParts.title.style.cssText;
        }
    }

    function syncFeaturedHolders() {
        if (syncing) {
            return;
        }

        syncing = true;
        var items = Array.prototype.slice.call(grid.querySelectorAll(".masonry-item"));
        var populatedItems = items.filter(hasPostContent);

        // Compact populated cards into the first slots so a skipped response row
        // cannot leave an empty, hoverable card between real posts.
        populatedItems.forEach(function (source, index) {
            var target = items[index];
            if (source !== target) {
                movePostContent(source, target);
            }
        });

        var populatedCount = populatedItems.length;
        items.forEach(function (item, index) {
            var populated = index < populatedCount && hasPostContent(item);
            if (populated) {
                if (item.style.pointerEvents !== "") {
                    item.style.pointerEvents = "";
                }
            } else {
                if (item.style.display !== "none") {
                    item.style.display = "none";
                }
                if (item.style.pointerEvents !== "none") {
                    item.style.pointerEvents = "none";
                }
                if (item.style.transform !== "") {
                    item.style.transform = "";
                }
            }
        });

        // Keep the existing three-column minimum while ensuring only real cards
        // participate in responsive display and hover behavior.
        if (typeof featuredGridCount !== "undefined") {
            featuredGridCount = populatedCount;
        }
        var columnCount = window.innerWidth < 768
            ? (populatedCount > 0 ? 1 : 0)
            : Math.max(minGridColumns, populatedCount);
        if (String(grid.style.columnCount) !== String(columnCount)) {
            grid.style.columnCount = columnCount;
        }

        var featuredPost = document.getElementById("featuredpost");
        if (featuredPost) {
            var featuredPostDisplay = populatedCount > 0 ? "" : "none";
            if (featuredPost.style.display !== featuredPostDisplay) {
                featuredPost.style.display = featuredPostDisplay;
            }
        }
        if (populatedCount > 0) {
            var indicatorCard = document.getElementById("indicatorCard");
            if (indicatorCard && indicatorCard.style.marginTop !== "0px") {
                indicatorCard.style.marginTop = "0px";
            }
        }
        syncing = false;
    }

    if (window.MutationObserver) {
        var observer = new MutationObserver(syncFeaturedHolders);
        observer.observe(grid, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ["style"]
        });
    }

    syncFeaturedHolders();
    window.addEventListener("resize", syncFeaturedHolders);
})();
