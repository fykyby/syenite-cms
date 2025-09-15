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

document.body.addEventListener("htmx:beforeOnLoad", function (event) {
    // Allow HTMX swapping of error responses
    if (event.detail.xhr.status >= 400 && event.detail.xhr.status < 600) {
        event.detail.shouldSwap = true;
        event.detail.isError = false;
    }

    // Replace URL after server redirect
    const redirectUrl = event.detail.xhr.responseURL;
    if (redirectUrl) {
        history.replaceState({}, "", redirectUrl);
    }
});
