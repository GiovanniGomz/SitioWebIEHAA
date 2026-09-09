<?php

namespace Iehaa\Usuarios\Classes;

use Closure;
use Illuminate\Http\Request;

/**
 * Protects plain Laravel routes (registered outside the CMS page/layout
 * lifecycle, e.g. the PDF/Excel report routes) with the same session
 * check used by the cpanel layout.
 */
class CpanelAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!CpanelAuth::check()) {
            return redirect('/login');
        }

        return $next($request);
    }
}
