<?php
/**
 * Plugin Name: Labdoo Security
 * Description: Security hardening: blocks REST API user enumeration.
 */

// Block /wp-json/wp/v2/users endpoint for unauthenticated requests (S4)
add_filter('rest_endpoints', function($endpoints) {
    if (!current_user_can('list_users')) {
        unset($endpoints['/wp/v2/users']);
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
});
