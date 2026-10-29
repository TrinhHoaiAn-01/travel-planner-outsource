(() => {
    const root = document.getElementById('travel-chat');
    if (!root) return;
    const panel = root.querySelector('.travel-chat__panel');
    const toggle = root.querySelector('.travel-chat__toggle');
    const close = root.querySelector('.travel-chat__close');
    const form = root.querySelector('form');
    const input = root.querySelector('textarea');
    const send = form.querySelector('button');
    const messages = root.querySelector('.travel-chat__messages');
    const status = root.querySelector('.travel-chat__status');
    const suggestions = root.querySelector('.travel-chat__suggestions');
    let loaded = false;
    let loading = false;

    function addMessage(role, content, recommendations = []) {
        const bubble = document.createElement('div');
        bubble.className = `travel-chat__message travel-chat__message--${role}`;
        bubble.textContent = content;
        messages.appendChild(bubble);
        recommendations.forEach(item => {
            // Only navigate to this application's public destination pages.
            if (typeof item.url !== 'string' || !item.url.startsWith('/destinations/') || item.url.startsWith('//')) return;
            const card = document.createElement('a');
            card.className = 'travel-chat__card';
            card.href = item.url;
            const name = document.createElement('strong');
            name.textContent = item.name;
            const detail = document.createElement('span');
            detail.textContent = `${item.city} · ${item.price}`;
            const action = document.createElement('span');
            action.textContent = 'Xem chi tiết →';
            card.append(name, detail, action);
            bubble.appendChild(card);
        });
        messages.scrollTop = messages.scrollHeight;
        return bubble;
    }
    function renderSuggestions(items) {
        suggestions.querySelectorAll('button').forEach(button => button.remove());
        (items || []).slice(0, 3).forEach(item => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.action = item.action;
            button.dataset.message = item.message;
            const label = document.createElement('span');
            label.textContent = item.label;
            const arrow = document.createElement('span');
            arrow.textContent = '↗';
            arrow.setAttribute('aria-hidden', 'true');
            button.append(label, arrow);
            suggestions.appendChild(button);
        });
    }
    function historyStatus(source) {
        status.textContent = source === 'session' ? 'Lịch sử hiện chỉ lưu trong phiên trình duyệt.' : '';
    }
    async function loadState() {
        if (loaded || loading) return;
        loading = true;
        send.disabled = true;
        status.textContent = 'Đang tải cuộc trò chuyện…';
        try {
            const response = await fetch(root.dataset.stateUrl, {headers: {'Accept': 'application/json'}, cache: 'no-store'});
            if (!response.ok) throw new Error('Không thể tải lịch sử. Đóng và mở lại để thử lại.');
            const data = await response.json();
            (data.history || []).forEach(item => {
                if (['user', 'assistant'].includes(item.role) && typeof item.content === 'string') addMessage(item.role, item.content, item.recommendations || []);
            });
            renderSuggestions(data.suggestions);
            historyStatus(data.history_source);
            loaded = true;
        } catch (error) {
            status.textContent = error.message;
        } finally {
            loading = false;
            send.disabled = false;
        }
    }
    suggestions.addEventListener('click', event => {
        const button = event.target.closest('button');
        if (!button || send.disabled) return;
        submitMessage(button.dataset.message, button.dataset.action);
    });
    function setOpen(open) {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        if (open) { input.focus(); messages.scrollTop = messages.scrollHeight; loadState(); }
        else toggle.focus();
    }
    toggle.addEventListener('click', () => setOpen(panel.hidden));
    close.addEventListener('click', () => setOpen(false));
    panel.addEventListener('keydown', event => {
        if (event.key === 'Escape') setOpen(false);
    });
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            form.requestSubmit();
        }
    });
    async function submitMessage(text, action = null) {
        const message = text.trim();
        if (!message || send.disabled) return;
        const originalInput = input.value;
        const userBubble = addMessage('user', message);
        input.value = '';
        send.disabled = true;
        status.textContent = 'Trợ lý đang trả lời…';
        try {
            const response = await fetch(root.dataset.url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({message, action})
            });
            const data = await response.json();
            if (!response.ok || !data.answer) throw new Error(data.message || 'Không thể gửi tin nhắn.');
            if (action) {
                // Simulated response pacing for catalogue buttons; no model is invoked.
                await new Promise(resolve => setTimeout(resolve, 1000 + Math.floor(Math.random() * 5001)));
            }
            addMessage('assistant', data.answer, data.recommendations || []);
            renderSuggestions(data.suggestions);
            historyStatus(data.history_source);
        } catch (error) {
            status.textContent = error.message || 'Lỗi kết nối. Vui lòng thử lại.';
            input.value = action ? originalInput : message;
            userBubble.remove();
        } finally {
            send.disabled = false;
            input.focus();
        }
    }
    form.addEventListener('submit', event => {
        event.preventDefault();
        submitMessage(input.value);
    });
})();
