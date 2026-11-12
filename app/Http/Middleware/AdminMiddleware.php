<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Xử lý kiểm tra quyền quản trị viên trước khi vào hệ thống Admin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra nếu người dùng chưa đăng nhập
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            return redirect()->guest(route('login', [], false))
                ->with('error', 'Vui lòng đăng nhập bằng tài khoản quản trị để tiếp tục.');
        }

        $user = Auth::user();

        // Kiểm tra nếu tài khoản đang bị khóa
        if (! $user || ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.');
        }

        // Kiểm tra vai trò của người dùng (phải là admin)
        if (! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bạn không có quyền truy cập vào khu vực quản trị hệ thống.',
                ], 403);
            }

            // Từ chối truy cập và phản hồi HTTP 403 Forbidden theo đúng đặc tả bảo mật trong Báo cáo
            abort(403, 'Bạn không có quyền truy cập vào khu vực quản trị hệ thống.');
        }

        return $next($request);
    }
}
