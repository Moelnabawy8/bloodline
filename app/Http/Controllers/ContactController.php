<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function show()
    {
        return view('pages.contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email'],
            'subject'    => ['required', 'string', 'max:255'],
            'message'    => ['required', 'string', 'max:5000'],
        ]);

        Contact::create($data);

        return back()->with('success', 'Your message has been sent. We will get back to you soon.');
    }
}
