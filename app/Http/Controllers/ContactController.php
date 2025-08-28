<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    /**
     * Store a new contact message from the public form
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|in:enrollment,academic,admission,facilities,other',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please check your form and try again.');
        }

        try {
            ContactMessage::create([
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'status' => 'unread'
            ]);

            return redirect()->to(url()->previous() . '#contact')->with('success', 'Thank you for your message! We will get back to you soon.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Something went wrong. Please try again later.');
        }
    }

    /**
     * Display contact messages for admin (excluding replied messages)
     */
    public function adminIndex(Request $request)
    {
        // Base query excludes replied messages
        $query = ContactMessage::where('status', '!=', 'replied');

        // Filter by status (only unread and read)
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by subject
        if ($request->has('subject') && $request->subject !== '') {
            $query->where('subject', $request->subject);
        }

        // Search functionality
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get statistics (excluding replied messages)
        $stats = [
            'total' => ContactMessage::where('status', '!=', 'replied')->count(),
            'unread' => ContactMessage::where('status', 'unread')->count(),
            'read' => ContactMessage::where('status', 'read')->count(),
        ];

        return view('admin.contact-us_messages', compact('messages', 'stats'));
    }

    /**
     * Show individual message
     */
    public function show(ContactMessage $message)
    {
        // Mark as read if unread
        if ($message->status === 'unread') {
            $message->markAsRead();
        }

        return response()->json([
            'success' => true,
            'message' => $message->append('subject_display')
        ]);
    }

    /**
     * Update message status
     */
    public function updateStatus(Request $request, ContactMessage $message)
    {
        $request->validate([
            'status' => 'required|in:unread,read,replied',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        $message->update([
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'read_at' => $request->status !== 'unread' ? now() : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message status updated successfully.'
        ]);
    }

    /**
     * Delete message
     */
    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return response()->json([
            'success' => true,
            'message' => 'Message deleted successfully.'
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_read,mark_replied,delete',
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:contact_messages,id'
        ]);

        $messages = ContactMessage::whereIn('id', $request->message_ids);

        switch ($request->action) {
            case 'mark_read':
                $messages->update([
                    'status' => 'read',
                    'read_at' => now()
                ]);
                $successMessage = 'Messages marked as read.';
                break;

            case 'mark_replied':
                $messages->update(['status' => 'replied']);
                $successMessage = 'Messages marked as replied.';
                break;

            case 'delete':
                $messages->delete();
                $successMessage = 'Messages deleted successfully.';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $successMessage
        ]);
    }
}
