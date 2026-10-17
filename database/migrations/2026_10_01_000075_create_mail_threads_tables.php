<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inbox Email: satu thread = satu rangkaian surat dengan satu lawan
        // bicara (contact_email). Terpisah dari Live Chat dan tiket.
        Schema::create('mail_threads', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->string('contact_email')->index();
            $table->string('contact_name')->nullable();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('chat_conversation_id')->nullable()
                ->constrained('chat_conversations')->nullOnDelete();
            $table->timestamp('idle_prompted_at')->nullable();
            $table->string('status', 20)->default('open')->index();   // open | closed
            $table->unsignedInteger('unread_count')->default(0);
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('mail_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mail_thread_id')->constrained()->cascadeOnDelete();
            $table->string('direction', 3);                            // in | out
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email');
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->string('message_id')->nullable()->index();         // Message-ID tanpa <>
            $table->foreignId('admin_id')->nullable()->constrained()->nullOnDelete();
            $table->json('attachments')->nullable();                   // [{path,name,mime,size}]
            $table->boolean('is_auto')->default(false);
            $table->timestamps();
        });

        // Pindahkan percakapan kanal email yang sudah ada ke Inbox Email.
        $chats = DB::table('chat_conversations')
            ->where('channel', 'email')
            ->whereNotNull('email')
            ->get();

        foreach ($chats as $chat) {
            $name = $chat->name ?: null;

            $threadId = DB::table('mail_threads')->insertGetId([
                'subject' => 'Live Chat — ' . ($name ?: $chat->email),
                'contact_email' => strtolower($chat->email),
                'contact_name' => $name,
                'client_id' => $chat->client_id,
                'chat_conversation_id' => $chat->id,
                'status' => $chat->status === 'closed' ? 'closed' : 'open',
                'unread_count' => (int) $chat->unread_for_admin,
                'last_message_at' => $chat->last_message_at,
                'created_at' => $chat->created_at,
                'updated_at' => now(),
            ]);

            $messages = DB::table('chat_messages')
                ->where('chat_conversation_id', $chat->id)
                ->whereIn('sender', ['user', 'admin'])
                ->orderBy('id')
                ->get();

            foreach ($messages as $message) {
                $isIncoming = $message->sender === 'user';

                DB::table('mail_messages')->insert([
                    'mail_thread_id' => $threadId,
                    'direction' => $isIncoming ? 'in' : 'out',
                    'from_email' => $isIncoming
                        ? strtolower($chat->email)
                        : (string) config('mail.from.address'),
                    'from_name' => $isIncoming ? $name : null,
                    'to_email' => $isIncoming
                        ? (string) config('mail.from.address')
                        : strtolower($chat->email),
                    'subject' => 'Live Chat',
                    'body' => $message->message ?: '(lampiran)',
                    'admin_id' => $isIncoming ? null : ($message->admin_id ?? null),
                    'created_at' => $message->created_at,
                    'updated_at' => $message->created_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_messages');
        Schema::dropIfExists('mail_threads');
    }
};
