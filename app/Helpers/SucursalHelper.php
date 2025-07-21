<?php

if (!function_exists('sucursal_activa_id')) {
    function sucursal_activa_id()
    {
        return session('sucursal_activa_id') ?? auth()->user()?->sucursal_id;
    }
}

