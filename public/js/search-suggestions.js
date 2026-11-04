(() => {
    'use strict';
    if (window.travelSearchSuggestionsLoaded) return;
    window.travelSearchSuggestionsLoaded = true;

    const selector = 'input[data-search-suggestions]';
    const panel = document.createElement('div');
    panel.className = 'search-suggestions';
    panel.id = 'search-suggestion-list';
    panel.setAttribute('role', 'listbox');
    panel.setAttribute('aria-label', 'Gợi ý tìm kiếm');
    panel.hidden = true;
    document.body.appendChild(panel);
    const status = document.createElement('span');
    status.setAttribute('role', 'status');
    status.style.cssText = 'position:absolute;width:1px;height:1px;overflow:hidden;clip-path:inset(50%)';
    document.body.appendChild(status);
    let input = null;
    let controller = null;
    let timer = null;
    let generation = 0;
    let suggestions = [];
    let active = -1;

    function cancel() {
        clearTimeout(timer);
        controller?.abort();
        generation++;
    }

    function hide() {
        cancel();
        panel.hidden = true;
        input?.setAttribute('aria-expanded', 'false');
        input?.removeAttribute('aria-activedescendant');
        active = -1;
    }

    function position() {
        if (!input || panel.hidden) return;
        if (!input.isConnected) return hide();
        const rect = input.getBoundingClientRect();
        const width = Math.min(Math.max(rect.width, 260), window.innerWidth - 16);
        panel.style.width = `${width}px`;
        panel.style.left = `${Math.max(8, Math.min(rect.left, window.innerWidth - width - 8))}px`;
        const below = window.innerHeight - rect.bottom - 12;
        const above = rect.top - 12;
        const placeAbove = below < 180 && above > below;
        panel.style.maxHeight = `${Math.max(60, Math.min(320, placeAbove ? above : below))}px`;
        panel.style.top = placeAbove ? 'auto' : `${rect.bottom + 6}px`;
        panel.style.bottom = placeAbove ? `${window.innerHeight - rect.top + 6}px` : 'auto';
    }

    function showMessage(message) {
        panel.replaceChildren();
        const entry = document.createElement('div');
        entry.className = 'search-suggestion-message';
        entry.textContent = message;
        panel.appendChild(entry);
        suggestions = [];
        active = -1;
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        input.removeAttribute('aria-activedescendant');
        status.textContent = message;
        position();
    }

    function setActive(index) {
        active = index;
        [...panel.querySelectorAll('[role="option"]')].forEach((option, optionIndex) => {
            option.setAttribute('aria-selected', String(optionIndex === active));
            if (optionIndex === active) {
                input.setAttribute('aria-activedescendant', option.id);
                option.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    function select(index) {
        const suggestion = suggestions[index];
        if (!suggestion || !input) return;
        const selectedInput = input;
        hide();
        // Bộ setter gốc cũng cập nhật đúng trạng thái input do React quản lý.
        Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value').set.call(selectedInput, suggestion.value);
        selectedInput.dispatchEvent(new Event('input', { bubbles: true }));
        selectedInput.dispatchEvent(new Event('change', { bubbles: true }));
        hide();
        selectedInput.form?.requestSubmit();
    }

    async function load(target, keyword, version) {
        controller = new AbortController();
        showMessage('Đang tìm gợi ý…');
        const url = new URL(`/search/suggestions/${encodeURIComponent(target.dataset.searchSuggestions)}`, window.location.origin);
        if (target.form) {
            const fields = new FormData(target.form);
            for (const [key, value] of fields) {
                if (!['search', 'keyword', 'query', 'page', '_token'].includes(key) && typeof value === 'string') {
                    url.searchParams.set(key, value);
                }
            }
        }
        url.searchParams.set('keyword', keyword);
        try {
            const response = await fetch(url, { headers: { Accept: 'application/json' }, signal: controller.signal });
            if (!response.ok) throw new Error('Search unavailable');
            const data = await response.json();
            if (version !== generation || input !== target || document.activeElement !== target) return;
            suggestions = data.suggestions;
            if (!Array.isArray(suggestions)) throw new Error('Invalid suggestions');
            if (!suggestions.length) return showMessage('Không tìm thấy gợi ý phù hợp.');
            panel.replaceChildren();
            active = -1;
            input.removeAttribute('aria-activedescendant');
            suggestions.forEach((suggestion, index) => {
                const option = document.createElement('div');
                option.className = 'search-suggestion-option';
                option.id = `search-suggestion-${index}`;
                option.setAttribute('role', 'option');
                option.setAttribute('aria-selected', 'false');
                const label = document.createElement('span');
                label.className = 'search-suggestion-label';
                label.textContent = suggestion.label;
                option.appendChild(label);
                if (suggestion.description) {
                    const description = document.createElement('span');
                    description.className = 'search-suggestion-description';
                    description.textContent = suggestion.description;
                    option.appendChild(description);
                }
                option.addEventListener('pointerdown', event => event.preventDefault());
                option.addEventListener('click', () => select(index));
                panel.appendChild(option);
            });
            status.textContent = `${suggestions.length} gợi ý tìm kiếm.`;
            position();
        } catch (error) {
            if (error.name !== 'AbortError' && version === generation && input === target && document.activeElement === target) {
                showMessage('Không thể tải gợi ý. Vui lòng thử lại sau.');
            }
        }
    }

    function schedule(target) {
        cancel();
        if (input !== target) {
            input?.setAttribute('aria-expanded', 'false');
            input?.removeAttribute('aria-activedescendant');
        }
        input = target;
        target.setAttribute('role', 'combobox');
        target.setAttribute('aria-autocomplete', 'list');
        target.setAttribute('aria-controls', panel.id);
        target.setAttribute('aria-expanded', 'false');
        target.setAttribute('autocomplete', 'off');
        target.setAttribute('maxlength', '200');
        panel.hidden = true;
        const keyword = target.value.trim();
        if (!keyword) return hide();
        const version = generation;
        timer = setTimeout(() => load(target, keyword, version), 250);
    }

    document.addEventListener('focusin', event => {
        if (event.target.matches(selector)) schedule(event.target);
    });
    document.addEventListener('input', event => {
        if (event.target.matches(selector) && !event.isComposing) schedule(event.target);
    });
    document.addEventListener('keydown', event => {
        if (event.target !== input || event.isComposing) return;
        if (event.key === 'Escape' || event.key === 'Tab') return hide();
        if (panel.hidden) return;
        if ((event.key === 'ArrowDown' || event.key === 'ArrowUp') && suggestions.length) {
            event.preventDefault();
            const next = active < 0 ? (event.key === 'ArrowDown' ? 0 : suggestions.length - 1)
                : (active + (event.key === 'ArrowDown' ? 1 : -1) + suggestions.length) % suggestions.length;
            setActive(next);
        } else if (event.key === 'Enter' && active >= 0) {
            event.preventDefault();
            select(active);
        }
    });
    document.addEventListener('focusout', event => { if (event.target === input) hide(); });
    document.addEventListener('pointerdown', event => {
        if (event.target !== input && !panel.contains(event.target)) hide();
    });
    document.addEventListener('submit', hide);
    document.addEventListener('change', event => {
        if (input?.form && event.target !== input && event.target.form === input.form) hide();
    });
    window.addEventListener('resize', position);
    window.addEventListener('scroll', position, true);
})();
