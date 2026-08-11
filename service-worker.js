// Root scope is required so the same background notification worker controls
// both the main site and /blog without an install-time network dependency.
importScripts('assets/js/pwabuilder-sw.js');
