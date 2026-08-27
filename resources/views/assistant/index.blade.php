<x-layouts.app title="AI Assistant">
    <div class="page-header">
        <div>
            <h1>AI Assistant</h1>
            <p class="subtitle">Ask about attendance, exams, fees, schedules or performance — read-only, tenant-scoped.</p>
        </div>
    </div>

    <div class="chat-shell">
        <aside class="chat-sidebar">
            <button class="btn btn-primary w-100 mb-3" id="newChat"><i class="bi bi-plus-lg me-1"></i> New chat</button>
            @if(auth()->user()->isSuperAdmin())
                <label class="form-label small text-muted">Provider</label>
                <select id="provider" class="form-select form-select-sm mb-3">
                    @foreach($providers as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
                </select>
            @endif
            <div class="text-muted small mb-2">Try asking</div>
            <div class="d-flex flex-column gap-2" id="suggestions">
                <span class="chat-suggestion" data-q="What is the attendance trend this week?"><i class="bi bi-calendar-check"></i> Attendance trend</span>
                <span class="chat-suggestion" data-q="Summarize exam performance for my class"><i class="bi bi-graph-up"></i> Exam performance</span>
                <span class="chat-suggestion" data-q="Which students have outstanding fees?"><i class="bi bi-cash-coin"></i> Outstanding fees</span>
                <span class="chat-suggestion" data-q="Draft a notice about the upcoming holiday"><i class="bi bi-megaphone"></i> Draft a notice</span>
            </div>
        </aside>

        <section class="chat-main">
            <div class="chat-messages" id="chat">
                <div class="msg bot">
                    <span class="avatar"><i class="bi bi-robot"></i></span>
                    <div class="bubble">Hi {{ auth()->user()->name }}! 👋 I can help you explore attendance, exams, fees and schedules. What would you like to know?</div>
                </div>
            </div>

            <div class="chat-input">
                <textarea id="query" class="form-control" rows="1" placeholder="Message the assistant…" aria-label="Your message"></textarea>
                <button class="btn btn-primary btn-icon" id="sendBtn" aria-label="Send"><i class="bi bi-send"></i></button>
            </div>
        </section>
    </div>

    @push('scripts')
    <script>
        (function () {
            const chat = document.getElementById('chat');
            const input = document.getElementById('query');
            const sendBtn = document.getElementById('sendBtn');
            const providerEl = document.getElementById('provider');

            function escapeHtml(s) { return s.replace(/[&<>"']/g, c => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c])); }
            function renderText(s) { return escapeHtml(s).replace(/`([^`]+)`/g, '<code>$1</code>'); }

            function addMsg(who, html) {
                const el = document.createElement('div');
                el.className = 'msg ' + who;
                const avatar = who === 'bot'
                    ? '<span class="avatar"><i class="bi bi-robot"></i></span>'
                    : '<span class="avatar"><i class="bi bi-person-fill"></i></span>';
                el.innerHTML = avatar + '<div class="bubble"></div>';
                const bubble = el.querySelector('.bubble');
                if (who === 'bot') bubble.innerHTML = html; else bubble.textContent = html;
                chat.appendChild(el);
                chat.scrollTop = chat.scrollHeight;
                return bubble;
            }

            function typewriter(bubble, text) {
                const safe = renderText(text);
                // Reveal in chunks for a streaming feel.
                bubble.innerHTML = '';
                let i = 0;
                const tokens = safe.split(/(?=<code>)/);
                const timer = setInterval(() => {
                    if (i >= tokens.length) { clearInterval(timer); addCopy(bubble.closest('.msg')); return; }
                    bubble.innerHTML += tokens[i++];
                    chat.scrollTop = chat.scrollHeight;
                }, 18);
            }

            function addCopy(msgEl) {
                if (!msgEl || msgEl.querySelector('.copy-btn')) return;
                const btn = document.createElement('button');
                btn.className = 'btn btn-sm btn-outline-secondary copy-btn mt-1';
                btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                btn.onclick = () => {
                    navigator.clipboard.writeText(msgEl.querySelector('.bubble').textContent);
                    btn.innerHTML = '<i class="bi bi-check2"></i> Copied';
                    setTimeout(() => btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy', 1500);
                };
                msgEl.querySelector('.bubble').after(btn);
            }

            function botTyping() {
                const el = document.createElement('div');
                el.className = 'msg bot';
                el.innerHTML = '<span class="avatar"><i class="bi bi-robot"></i></span><div class="bubble"><span class="typing"><span></span><span></span><span></span></span></div>';
                chat.appendChild(el); chat.scrollTop = chat.scrollHeight;
                return el;
            }

            function ask(q) {
                if (!q.trim()) return;
                addMsg('user', q);
                input.value = '';
                sendBtn.disabled = true;
                const typing = botTyping();
                const payload = { query: q };
                if (providerEl) payload.provider = providerEl.value;
                axios.post('{{ route('assistant.ask') }}', payload)
                    .then(r => {
                        typing.remove();
                        const txt = (r.data.answer || '') + (r.data.intent ? '  \n\n_intent: ' + r.data.intent + '_' : '');
                        typewriter(addMsg('bot', ''), txt);
                    })
                    .catch(() => { typing.remove(); addMsg('bot', '⚠️ Something went wrong. Please try again.'); })
                    .finally(() => sendBtn.disabled = false);
            }

            sendBtn.addEventListener('click', () => ask(input.value));
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); ask(input.value); }
            });
            document.querySelectorAll('#suggestions .chat-suggestion').forEach(s =>
                s.addEventListener('click', () => ask(s.getAttribute('data-q'))));
            document.getElementById('newChat').addEventListener('click', () => {
                chat.innerHTML = '<div class="msg bot"><span class="avatar"><i class="bi bi-robot"></i></span><div class="bubble">New conversation started. How can I help?</div></div>';
            });
        })();
    </script>
    @endpush
</x-layouts.app>
