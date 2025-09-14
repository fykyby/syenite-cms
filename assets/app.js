/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import "./styles/app.css";
import "./frankenui-core.iife.js";
import "./frankenui-icon.iife.js";
import "./htmx.min.js";

// Allow HTMX swapping of error responses
document.body.addEventListener("htmx:beforeOnLoad", function (evt) {
    if (evt.detail.xhr.status >= 400 && evt.detail.xhr.status < 600) {
        evt.detail.shouldSwap = true;
        evt.detail.isError = false;
    }
});
