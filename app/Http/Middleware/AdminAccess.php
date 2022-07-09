<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class AdminAccess
{
    private $response = [];

    private function basic_responce()
    {
        return [
            'timestamp' => Carbon::now()->toDateTimeString(),
            'status' => 200,
            'message' => '',
            'results' => []
        ];
    }

    public function __construct()
    {
        $this->response = $this->basic_responce();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->user()->is('Admin')) {
            $this->response['status'] = 403;
            $this->response['message'] = 'Limited rights';
            return response()->json($this->response, $this->response['status']);
        }
        return $next($request);
    }
}
