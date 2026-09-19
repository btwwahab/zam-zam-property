<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use App\Support\MediaStore;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        return view('admin.team.index', ['members' => TeamMember::orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.team.form', ['member' => new TeamMember()]);
    }

    public function store(Request $request)
    {
        TeamMember::create($this->validated($request, null));

        return redirect()->route('admin.team.index')->with('ok', 'Team member added.');
    }

    public function edit(TeamMember $member)
    {
        return view('admin.team.form', compact('member'));
    }

    public function update(Request $request, TeamMember $member)
    {
        $oldImage = $member->image;
        $member->update($this->validated($request, $member));

        // If the photo was replaced, remove the old file (unless something else uses it).
        if ($oldImage && $oldImage !== $member->image) {
            MediaStore::forgetImageIfUnused($oldImage);
        }

        return redirect()->route('admin.team.index')->with('ok', 'Team member updated.');
    }

    public function destroy(TeamMember $member)
    {
        $image = $member->image;
        $member->delete();
        MediaStore::forgetImageIfUnused($image);

        return redirect()->route('admin.team.index')->with('ok', 'Team member removed.');
    }

    private function validated(Request $request, ?TeamMember $member): array
    {
        $v = $request->validate([
            'name' => 'required|string|max:150',
            'role' => 'nullable|string|max:120',
            'phone' => 'nullable|string|max:60',
            'whatsapp' => 'nullable|string|max:40',
            'image_file' => 'nullable|image|max:8192',
            'sort_order' => 'nullable|integer',
        ], [
            'image_file.image' => 'The photo must be a JPG, PNG or WEBP image.',
            'image_file.max' => 'The photo is too large (max 8 MB).',
        ]);

        unset($v['image_file']);

        if ($request->hasFile('image_file')) {
            $base = MediaStore::image($request->file('image_file'), $request->input('name') ?: 'team-member');

            if (! $base) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'image_file' => 'That photo could not be saved — please try a different file.',
                ]);
            }
            $v['image'] = $base;
        }

        $v['sort_order'] = $v['sort_order'] ?? ($member?->sort_order ?? (TeamMember::max('sort_order') + 1));

        return $v;
    }
}
