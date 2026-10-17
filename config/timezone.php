<?php

// Read timezone from .env so deployments can configure it without editing PHP.
$timezone = env('APP_TIMEZONE', 'Asia/Jakarta');
