<x-layouts.app title="AI Assistant">
    <h3 class="fw-bold mb-3"><i class="bi bi-robot text-primary"></i> AI Academic Assistant</h3>

    <div class="card card-stat">
        <div class="card-body">
            <div class="mb-2">
                @if(auth()->user()->isSuperAdmin())
                <label class="form-label small">Provider</label>
                <select id="provider" class="form-select form-select-sm d-inline w-auto">
                    @foreach($providers as $p)<option value="{{ $p }}">{{ ucfirst($p) }}</option>@endforeach
                </select>
                @endif
            </div>
            <div id="chat" class="border rounded p-3 mb-3" style="height:360px; overflow-y:auto; background:#fff;">
                <div class="text-muted small">Ask me anything about attendance, exams, fees or schedules. I am read-only.</div>
            </div>
            <form id="askForm" class="d-flex gap-2">
                <input type="text" id="query" class="form-control" placeholder="e.g. What is my attendance?" required>
                <button class="btn btn-primary" type="submit">Ask</button>
            </form>
            <div class="mt-2">
                @foreach(['What is my attendance?','My next exam?','My CGPA?','Outstanding fees?'] as $q)
                    <button type="button" class="btn btn-sm btn-outline-secondary quick">{{ $q }}</button>
                @endforeach
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const chat = document.getElementById('chat');
        function addBubble(text, who) {
            const div = document.createElement('div');
            div.className = 'mb-2 p-2 rounded ' + (who === 'user' ? 'bg-primary text-white ms-auto' : 'bg-light me-auto');
            div.style.maxWidth = '75%';
            div.textContent = text;
            chat.appendChild(div);
            chat.scrollTop = chat.scrollHeight;
        }
        document.getElementById('askForm').addEventListener('submit', function(e){
            e.preventDefault();
            const q = document.getElementById('query').value;
            if(!q) return;
            addBubble(q, 'user');
            document.getElementById('query').value = '';
            const btn = this.querySelector('button'); btn.disabled = true;
            const providerEl = document.getElementById('provider');
            const payload = { query: q };
            if (providerEl) { payload.provider = providerEl.value; }
            axios.post('{{ route('assistant.ask') }}', payload)
                .then(r => addBubble(r.data.answer + '  [' + r.data.intent + ']', 'bot'))
                .catch(() => addBubble('Something went wrong.', 'bot'))
                .finally(() => btn.disabled = false);
        });
        document.querySelectorAll('.quick').forEach(b => b.addEventListener('click', () => {
            document.getElementById('query').value = b.textContent; document.getElementById('askForm').requestSubmit();
        }));
    </script>
    @endpush
</x-layouts.app>
