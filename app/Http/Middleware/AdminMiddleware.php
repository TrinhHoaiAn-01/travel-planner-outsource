<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
        if (!auth()->check()) {
            return redirect()->guest(route('login', [], false))
                ->with('error', 'Vui lòng đăng nhập bằng tài khoản quản trị để tiếp tục.');
        }

        // Kiểm tra vai trò của người dùng (phải là admin)
        $user = auth()->user();
        if (!$user || $user->role !== 'admin') {
            // Từ chối truy cập và phản hồi HTTP 403 Forbidden theo đúng đặc tả bảo mật trong Báo cáo
            abort(403, 'Bạn không có quyền truy cập vào khu vực quản trị hệ thống.');
        }

        return $next($request);
    }
}
