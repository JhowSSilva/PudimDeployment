<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\User;
use App\Notifications\UserMentioned;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /**
     * Store a new comment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
            'body' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        // Extract mentions from body (@username)
        $mentions = $this->extractMentions($validated['body']);

        $comment = Comment::create([
            'team_id' => Auth::user()->currentTeam->id,
            'user_id' => Auth::id(),
            'commentable_type' => $validated['commentable_type'],
            'commentable_id' => $validated['commentable_id'],
            'body' => $validated['body'],
            'parent_id' => $validated['parent_id'] ?? null,
            'mentions' => $mentions,
        ]);

        // Send notifications to mentioned users
        if (!empty($mentions)) {
            $users = User::whereIn('id', $mentions)->get();
            foreach ($users as $user) {
                if ($user->id !== Auth::id()) {
                    $user->notify(new UserMentioned(
                        $comment,
                        $validated['commentable_type'],
                        $validated['commentable_id']
                    ));
                }
            }
        }

        return response()->json([
            'success' => true,
            'comment' => $comment->load('user'),
            'message' => 'Comment added successfully',
        ]);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, Comment $comment)
    {
        if (!$comment->canBeEditedBy(Auth::user())) {
            abort(403, 'You cannot edit this comment');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $mentions = $this->extractMentions($validated['body']);

        $comment->update([
            'body' => $validated['body'],
            'mentions' => $mentions,
        ]);

        $comment->markAsEdited();

        return response()->json([
            'success' => true,
            'comment' => $comment->fresh(['user']),
            'message' => 'Comment updated successfully',
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment)
    {
        if (!$comment->canBeDeletedBy(Auth::user())) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete this comment',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Get comments for a resource (AJAX).
     */
    public function getComments(Request $request)
    {
        $validated = $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
        ]);

        $comments = Comment::where('commentable_type', $validated['commentable_type'])
            ->where('commentable_id', $validated['commentable_id'])
            ->where('team_id', Auth::user()->currentTeam->id)
            ->topLevel()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        return response()->json([
            'comments' => $comments,
        ]);
    }

    /**
     * Extract user mentions from comment body.
     */
    protected function extractMentions(string $body): array
    {
        $team = Auth::user()->currentTeam;
        $userIds = [];

        // Match @username or @"User Name"
        preg_match_all('/@([\w]+)|@"([^"]+)"/', $body, $matches);

        $mentions = array_merge($matches[1], $matches[2]);
        $mentions = array_filter($mentions);

        if (empty($mentions)) {
            return [];
        }

        // Find users by name or email
        $users = $team->allUsers()->filter(function ($user) use ($mentions) {
            foreach ($mentions as $mention) {
                if (stripos($user->name, $mention) !== false || 
                    stripos($user->email, $mention) !== false) {
                    return true;
                }
            }
            return false;
        });

        return $users->pluck('id')->toArray();
    }
}
