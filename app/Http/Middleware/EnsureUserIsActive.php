<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Xử lý kiểm tra người dùng đã đăng nhập và tài khoản ở trạng thái hoạt động (is_active = true).
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
                ->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = Auth::user();

        // Kiểm tra nếu tài khoản người dùng đang bị khóa, vô hiệu hóa hoặc đã xóa mềm
        if (! $user || ! $user->is_active || $user->trashed()) {
            // Đăng xuất và hủy phiên làm việc ngay lập tức để bảo vệ an toàn
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Tài khoản của bạn đã bị khóa hoặc ngừng hoạt động. Vui lòng liên hệ quản trị viên.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Tài khoản của bạn đã bị khóa hoặc ngừng hoạt động. Vui lòng liên hệ quản trị viên.');
        }

        return $next($request);
    }
}
