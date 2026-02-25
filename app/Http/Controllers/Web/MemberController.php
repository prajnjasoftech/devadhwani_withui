<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\Role;
use App\Services\MemberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    protected MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    public function index(Request $request): Response
    {
        $temple = auth()->user();

        $query = Member::with('temple')
            ->where('temple_id', $temple->id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $members = $query->orderByDesc('id')->paginate(10)->withQueryString();

        return Inertia::render('Member/Index', [
            'members' => $members,
            'filters' => [
                'search' => $request->search ?? '',
            ],
        ]);
    }

    public function create(): Response
    {
        $temple = auth()->user();
        $roles = Role::where('temple_id', $temple->id)->get(['id', 'role_name']);

        return Inertia::render('Member/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        $temple = auth()->user();

        $data = $request->validated();
        $data['temple_id'] = $temple->id;

        $this->memberService->create($data);

        return redirect()->route('members.index')->with('success', 'Member created successfully.');
    }

    public function edit($id): Response
    {
        $temple = auth()->user();
        $member = $this->memberService->findForTemple($id, $temple->id);

        if (! $member) {
            abort(404);
        }

        $roles = Role::where('temple_id', $temple->id)->get(['id', 'role_name']);

        return Inertia::render('Member/Edit', [
            'member' => $member,
            'roles' => $roles,
        ]);
    }

    public function update(UpdateMemberRequest $request, $id): RedirectResponse
    {
        $temple = auth()->user();
        $member = $this->memberService->findForTemple($id, $temple->id);

        if (! $member) {
            abort(404);
        }

        $this->memberService->update($member, $request->validated());

        return redirect()->route('members.index')->with('success', 'Member updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $temple = auth()->user();
        $member = $this->memberService->findForTemple($id, $temple->id);

        if (! $member) {
            abort(404);
        }

        $this->memberService->delete($member);

        return redirect()->route('members.index')->with('success', 'Member deleted successfully.');
    }
}
