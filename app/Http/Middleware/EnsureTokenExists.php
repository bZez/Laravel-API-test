<?php

namespace App\Http\Middleware;

use App\Builders\OperatorBuilder;
use App\Models\Operator;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

final class EnsureTokenExists
{
    public function handle(Request $request, Closure $next): Response|JsonResponse|RedirectResponse
    {
        if (null === $request->bearerToken()) {
            return new Response(null, 401);
        }
        $operator = Operator::query()
            ->tap(function (OperatorBuilder $qb) use ($request): void {
                $qb->whereAccessTokenIs($request->bearerToken());
            })->first();
        $request->setUserResolver(function () use ($operator) {
            return $operator;
        });
        if (null === $request->user()) {
            return new Response(null, 401);
        }

        return $next($request);
    }
}
