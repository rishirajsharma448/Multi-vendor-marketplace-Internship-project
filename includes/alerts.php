<?php
// Vyapar Setu Alerts Component

require_once __DIR__ . '/session.php';

function displayAlerts() {
    $flash = getFlash();
    if ($flash) {
        $type = sanitize($flash['type']);
        $message = sanitize($flash['message']);
        $icon = 'bi-info-circle-fill';
        if ($type === 'success') $icon = 'bi-check-circle-fill';
        if ($type === 'danger') $icon = 'bi-exclamation-triangle-fill';
        if ($type === 'warning') $icon = 'bi-exclamation-circle-fill';
        
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show d-flex align-items-center shadow-sm border-0 my-3" role="alert">';
        echo '  <i class="' . $icon . ' me-2 fs-5"></i>';
        echo '  <div>' . $message . '</div>';
        echo '  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}
