<?php
// ─── GitHub OAuth ──────────────────────────────────────────────────
// 1. Ve a https://github.com/settings/developers
// 2. "New OAuth App"
// 3. Homepage URL: http://localhost:8080/tienda_gamer
// 4. Authorization callback URL:
//    http://localhost:8080/tienda_gamer/controllers/auth_controller.php?action=oauth_github
// 5. Copia Client ID y Client Secret aquí abajo
define('GITHUB_CLIENT_ID',     'TU_GITHUB_CLIENT_ID');
define('GITHUB_CLIENT_SECRET', 'TU_GITHUB_CLIENT_SECRET');
define('GITHUB_REDIRECT_URI',  'http://localhost:8080/tienda_gamer/controllers/auth_controller.php?action=oauth_github');
