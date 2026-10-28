<link rel="stylesheet" href="{{ asset('css/chat-widget.css') }}">
<div class="travel-chat" id="travel-chat" data-url="{{ route('chat.store') }}">
    <section class="travel-chat__panel" id="travel-chat-panel" aria-label="Trợ lý du lịch" hidden>
        <header class="travel-chat__header">
            <div class="travel-chat__avatar" aria-hidden="true">✦</div>
            <div><strong>Trợ lý du lịch</strong><span>Travel Planner · Sẵn sàng hỗ trợ</span></div>
            <button type="button" class="travel-chat__close" aria-label="Đóng trò chuyện">×</button>
        </header>
        <div class="travel-chat__messages" role="log" aria-live="polite" aria-relevant="additions">
            <div class="travel-chat__welcome"><span>✦</span><strong>Xin chào! Bạn muốn đi đâu?</strong><p>Hỏi mình về điểm đến, nơi lưu trú hoặc ý tưởng cho chuyến đi nhé.</p></div>
        </div>
        <div class="travel-chat__status" role="status" aria-live="polite"></div>
        <form class="travel-chat__form">
            <label class="travel-chat__sr-only" for="travel-chat-input">Câu hỏi của bạn</label>
            <textarea id="travel-chat-input" rows="1" maxlength="2000" placeholder="Nhập câu hỏi của bạn..." required></textarea>
            <button type="submit" aria-label="Gửi câu hỏi"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12 20 4l-5 16-3-6-8-2Zm8 2 8-10" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></button>
        </form>
        <p class="travel-chat__note">Thông tin từ AI có thể cần được kiểm tra lại.</p>
    </section>
    <button type="button" class="travel-chat__toggle" aria-expanded="false" aria-controls="travel-chat-panel">
        <span aria-hidden="true">✦</span> <span>Hỏi trợ lý du lịch</span>
    </button>
</div>
<script>window.travelChatHistory = @json(session('travel_chat_history', []));</script>
<script src="{{ asset('js/chat-widget.js') }}" defer></script>
