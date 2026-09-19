<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function index(Request $request)
    {
        $q = Enquiry::query()->latest();

        if ($s = $request->get('q')) {
            $q->where(fn ($w) => $w->where('name', 'like', "%$s%")->orWhere('phone', 'like', "%$s%"));
        }
        if ($st = $request->get('status')) {
            $q->where('status', $st);
        }
        if ($i = $request->get('interest')) {
            $q->where('interest', $i);
        }

        return view('admin.enquiries.index', [
            'enquiries' => $q->paginate(20)->withQueryString(),
            'counts' => [
                'new' => Enquiry::where('status', 'new')->count(),
                'contacted' => Enquiry::where('status', 'contacted')->count(),
                'closed' => Enquiry::where('status', 'closed')->count(),
            ],
            'filters' => $request->only(['q', 'status', 'interest']),
        ]);
    }

    public function update(Request $request, Enquiry $enquiry)
    {
        $data = $request->validate(['status' => 'required|in:new,contacted,closed']);
        $enquiry->update($data);

        return back()->with('ok', 'Enquiry marked as '.$data['status'].'.');
    }

    public function destroy(Enquiry $enquiry)
    {
        $enquiry->delete();

        return back()->with('ok', 'Enquiry deleted.');
    }
}
