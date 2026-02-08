@props(['commentableType', 'commentableId', 'parentId' => null])

<div class="comment-form">
    <form 
        onsubmit="submitComment(event, '{{ $commentableType }}', {{ $commentableId }}, {{ $parentId ?? 'null' }})"
        class="space-y-3"
    >
        <div>
            <textarea 
                name="body"
                rows="3"
                placeholder="Escreva um comentário... (use @ para mencionar alguém)"
                required
                maxlength="5000"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                oninput="updateCharCount(this)"
            ></textarea>
            <div class="flex items-center justify-between mt-1">
                <span class="text-xs text-gray-500">
                    Use @nome para mencionar membros da equipe
                </span>
                <span class="char-count text-xs text-gray-500">0 / 5000</span>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button 
                type="submit"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition"
            >
                {{ $parentId ? 'Responder' : 'Comentar' }}
            </button>
            @if($parentId)
                <button 
                    type="button"
                    onclick="toggleReplyForm({{ $parentId }})"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition"
                >
                    Cancelar
                </button>
            @endif
        </div>
    </form>
</div>

<script>
function updateCharCount(textarea) {
    const charCount = textarea.closest('.comment-form').querySelector('.char-count');
    charCount.textContent = `${textarea.value.length} / 5000`;
}

function submitComment(event, commentableType, commentableId, parentId) {
    event.preventDefault();
    
    const form = event.target;
    const textarea = form.querySelector('textarea[name="body"]');
    const body = textarea.value;
    
    if (!body.trim()) {
        return;
    }
    
    const submitButton = form.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Enviando...';
    
    fetch('/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            commentable_type: commentableType,
            commentable_id: commentableId,
            parent_id: parentId,
            body: body
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload comments
            loadComments(commentableType, commentableId);
            
            // Clear form
            textarea.value = '';
            updateCharCount(textarea);
            
            // Hide reply form if it's a reply
            if (parentId) {
                toggleReplyForm(parentId);
            }
        } else {
            alert('Erro ao enviar comentário');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao enviar comentário');
    })
    .finally(() => {
        submitButton.disabled = false;
        submitButton.textContent = parentId ? 'Responder' : 'Comentar';
    });
}
</script>
