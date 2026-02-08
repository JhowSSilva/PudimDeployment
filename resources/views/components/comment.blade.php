@props(['comment', 'depth' => 0])

<div class="comment-item {{ $depth > 0 ? 'ml-12' : '' }}" data-comment-id="{{ $comment->id }}">
    <div class="flex gap-3 p-4 bg-white rounded-lg border border-gray-200 hover:border-gray-300 transition">
        <!-- Avatar -->
        <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-semibold">
                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
            </div>
        </div>

        <!-- Comment Content -->
        <div class="flex-1 min-w-0">
            <!-- Header -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-900">{{ $comment->user->name }}</span>
                    <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                    @if($comment->is_edited)
                        <span class="text-xs text-gray-400 italic">(editado)</span>
                    @endif
                </div>

                @if(auth()->id() === $comment->user_id || $comment->canBeDeletedBy(auth()->user()))
                    <div class="flex items-center gap-2">
                        @if($comment->canBeEditedBy(auth()->user()))
                            <button 
                                onclick="editComment({{ $comment->id }})"
                                class="text-xs text-blue-600 hover:text-blue-800 transition"
                            >
                                Editar
                            </button>
                        @endif
                        <button 
                            onclick="deleteComment({{ $comment->id }})"
                            class="text-xs text-red-600 hover:text-red-800 transition"
                        >
                            Excluir
                        </button>
                    </div>
                @endif
            </div>

            <!-- Body -->
            <div class="comment-body text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->body }}</div>

            <!-- Edit Form (hidden by default) -->
            <form 
                id="edit-form-{{ $comment->id }}" 
                class="hidden mt-3"
                onsubmit="updateComment(event, {{ $comment->id }})"
            >
                <textarea 
                    id="edit-textarea-{{ $comment->id }}"
                    rows="3"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                >{{ $comment->body }}</textarea>
                <div class="flex items-center gap-2 mt-2">
                    <button 
                        type="submit"
                        class="px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded transition"
                    >
                        Salvar
                    </button>
                    <button 
                        type="button"
                        onclick="cancelEdit({{ $comment->id }})"
                        class="px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded transition"
                    >
                        Cancelar
                    </button>
                </div>
            </form>

            <!-- Mentions -->
            @if(!empty($comment->mentions))
                <div class="flex items-center gap-1 mt-2 text-xs text-gray-500">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"/>
                    </svg>
                    <span>Mencionou {{ count($comment->mentions) }} {{ count($comment->mentions) === 1 ? 'pessoa' : 'pessoas' }}</span>
                </div>
            @endif

            <!-- Reply Button -->
            @if($depth < 2)
                <button 
                    onclick="toggleReplyForm({{ $comment->id }})"
                    class="mt-2 text-xs text-blue-600 hover:text-blue-800 transition"
                >
                    Responder
                </button>

                <!-- Reply Form (hidden by default) -->
                <div id="reply-form-{{ $comment->id }}" class="hidden mt-3">
                    <x-comment-form 
                        :commentable-type="$comment->commentable_type" 
                        :commentable-id="$comment->commentable_id"
                        :parent-id="$comment->id"
                    />
                </div>
            @endif
        </div>
    </div>

    <!-- Nested Replies -->
    @if($comment->replies && $comment->replies->count() > 0 && $depth < 2)
        <div class="mt-3 space-y-3">
            @foreach($comment->replies as $reply)
                <x-comment :comment="$reply" :depth="$depth + 1" />
            @endforeach
        </div>
    @endif
</div>

<script>
function editComment(commentId) {
    const body = document.querySelector(`[data-comment-id="${commentId}"] .comment-body`);
    const form = document.getElementById(`edit-form-${commentId}`);
    
    body.classList.add('hidden');
    form.classList.remove('hidden');
}

function cancelEdit(commentId) {
    const body = document.querySelector(`[data-comment-id="${commentId}"] .comment-body`);
    const form = document.getElementById(`edit-form-${commentId}`);
    
    body.classList.remove('hidden');
    form.classList.add('hidden');
}

function updateComment(event, commentId) {
    event.preventDefault();
    
    const textarea = document.getElementById(`edit-textarea-${commentId}`);
    const body = textarea.value;
    
    fetch(`/comments/${commentId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ body })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const bodyElement = document.querySelector(`[data-comment-id="${commentId}"] .comment-body`);
            bodyElement.textContent = body;
            cancelEdit(commentId);
            
            // Add edited indicator if not present
            const header = document.querySelector(`[data-comment-id="${commentId}"] .flex.items-center.gap-2`);
            if (!header.querySelector('.italic')) {
                const edited = document.createElement('span');
                edited.className = 'text-xs text-gray-400 italic';
                edited.textContent = '(editado)';
                header.appendChild(edited);
            }
        } else {
            alert('Erro ao atualizar comentário');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao atualizar comentário');
    });
}

function deleteComment(commentId) {
    if (!confirm('Tem certeza que deseja excluir este comentário?')) {
        return;
    }
    
    fetch(`/comments/${commentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`[data-comment-id="${commentId}"]`).remove();
        } else {
            alert('Erro ao excluir comentário');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Erro ao excluir comentário');
    });
}

function toggleReplyForm(commentId) {
    const form = document.getElementById(`reply-form-${commentId}`);
    form.classList.toggle('hidden');
}
</script>
