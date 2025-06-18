<?php
// flash.php
function flash(string $type = '', string $message = '')
{
    if (!session_id()) {
        session_start();
    }

    if ($type && $message) {
        $_SESSION['flash'] = compact('type', 'message');
    } elseif (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    return null;
}
