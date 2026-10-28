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

    function addMessage(role, content) {
        const bubble = document.createElement('div');
        bubble.className = `travel-chat__message travel-chat__message--${role}`;
        bubble.textContent = content;
        messages.appendChild(bubble);
        messages.scrollTop = messages.scrollHeight;
    }
    (window.travelChatHistory || []).forEach(item => {
        if (['user', 'assistant'].includes(item.role) && typeof item.content === 'string') addMessage(item.role, item.content);
    });
    function setOpen(open) {
        panel.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        if (open) { input.focus(); messages.scrollTop = messages.scrollHeight; }
        else toggle.focus();
    }
    toggle.addEventListener('click', () => setOpen(panel.hidden));
    close.addEventListener('click', () => setOpen(false));
    input.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            form.requestSubmit();
        }
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message || send.disabled) return;
        addMessage('user', message);
        input.value = '';
        send.disabled = true;
        status.textContent = 'Trợ lý đang trả lời…';
        try {
            const response = await fetch(root.dataset.url, {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
                body: JSON.stringify({message})
            });
            const data = await response.json();
            if (!response.ok || !data.answer) throw new Error(data.message || 'Không thể gửi tin nhắn.');
            addMessage('assistant', data.answer);
            status.textContent = '';
        } catch (error) {
            status.textContent = error.message || 'Lỗi kết nối. Vui lòng thử lại.';
            input.value = message;
            messages.lastElementChild.remove();
        } finally {
            send.disabled = false;
            input.focus();
        }
    });
})();
