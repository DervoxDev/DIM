<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ContactFormRequest;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function send(ContactFormRequest $request)
    {
        try {
            $validatedData = $request->validated();
            
            Mail::send(new ContactFormMail($validatedData));
            
            return back()
                ->with('success', __('contact.messages.success'))
                ->with('form_submitted', true);
        } catch (\Exception $e) {
            return back()
                ->with('error', __('contact.messages.error'))
                ->with('form_submitted', true)
                ->withInput();
        }
    }
    
}
