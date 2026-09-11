<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: HealthCheckToken
 *
 * Protege o endpoint /up de monitoramento de disponibilidade.
 * Requer o header "X-Health-Token" com o valor configurado em APP_HEALTH_TOKEN.
 *
 * Se APP_HEALTH_TOKEN não estiver definido, o endpoint fica inacessível em produção
 * mas permanece aberto em ambiente local/testing para facilitar o desenvolvimento.
 */
class HealthCheckToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // Em ambiente local ou de testes, não exige token
        if (app()->environment('local', 'testing')) {
            return $next($request);
        }

        $expectedToken = config('app.health_token');

        if (empty($expectedToken)) {
            // Se o token não estiver configurado em produção, bloqueia por segurança
            abort(404);
        }

        if ($request->header('X-Health-Token') !== $expectedToken) {
            abort(401, 'Unauthorized');
        }

        return $next($request);
    }
}
