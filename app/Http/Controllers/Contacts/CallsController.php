<?php

namespace App\Http\Controllers\Contacts;

use App\Models\Contact\Call;
use Illuminate\Http\Request;
use App\Models\Contact\Contact;
use App\Http\Controllers\Controller;
use App\Services\Contact\Call\CreateCall;
use App\Services\Contact\Call\DestroyCall;

class CallsController extends Controller
{
    /**
     * Display the list of calls.
     *
     * @param  Contact $contact
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Contact $contact)
    {
        $calls = $contact->calls()->get();

        return CallResource::collection($calls);
    }

    /**
     * Store a call.
     *
     * @param  Contact $contact
     * @return Call
     */
    public function store(Request $request, Contact $contact)
    {
        return (new CreateCall)->execute([
            'account_id' => auth()->user()->account->id,
            'contact_id' => $contact->id,
            'content' => $request->get('content'),
            'called_at' => $request->get('called_at'),
        ]);
    }

    /**
     * Delete the call.
     *
     * @param Request $request
     * @param Call $call
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Call $call)
    {
        $data = [
            'account_id' => auth()->user()->account->id,
            'call_id' => $call->id,
        ];

        try {
            (new DestroyCall)->execute($data);
        } catch (\Exception $e) {
            return $this->respondNotFound();
        }
    }
}
