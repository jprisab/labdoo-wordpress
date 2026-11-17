<?php
/**
 * Plugin Name: Labdoo Chatbot Mobile Fix
 * Description: Fixes horizontal overflow on mobile caused by the AI Engine chatbot fixed-width window.
 */

add_action('wp_head', function () {
    echo '<style>
@media (max-width: 760px) {
  .mwai-chatgpt-theme.mwai-window {
    width: calc(100vw - 40px) !important;
    max-width: calc(100vw - 40px) !important;
    right: 20px !important;
    left: auto !important;
  }
}
</style>';
});
