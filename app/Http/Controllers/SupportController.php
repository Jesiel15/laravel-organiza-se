<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    // Lista os chamados do usuário logado (ou TODOS se for ADM)
    public function index(Request $request)
    {
        $user = $request->attributes->get('authUser');

        $query = Ticket::with(['user:id,name', 'messages.user:id,name']);

        // Se NÃO for admin, filtra apenas os tickets dele
        if (!$user->is_admin) {
            $query->where('user_id', $user->id);
        }

        $tickets = $query->orderBy('updated_at', 'desc')->get();

        return response()->json($tickets);
    }

    // Cria um novo ticket
    public function store(Request $request)
    {
        $user = $request->attributes->get('authUser');

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'subject' => $request->subject,
            'status' => 'open',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'is_admin' => (bool) $user->is_admin,
        ]);

        return response()->json($ticket->load('messages'), 201);
    }

    // Responder um ticket existente (Usuário ou Admin)
    public function reply(Request $request, $ticketId)
    {
        $user = $request->attributes->get('authUser');

        $request->validate(['message' => 'required|string']);

        $ticket = Ticket::findOrFail($ticketId);

        // Usuário comum só pode responder o próprio chamado
        if (!$user->is_admin && $ticket->user_id !== $user->id) {
            return response()->json(['msg' => 'Acesso negado.'], 403);
        }

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $request->message,
            'is_admin' => (bool) $user->is_admin,
        ]);

        // Se o admin responder, muda status para in_progress
        if ($user->is_admin && $ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        $ticket->touch(); // Atualiza 'updated_at' do ticket

        return response()->json($message);
    }
}