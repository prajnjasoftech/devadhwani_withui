<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\Temple;
use App\Services\MemberService;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    protected MemberService $memberService;

    public function __construct(MemberService $memberService)
    {
        $this->memberService = $memberService;
    }

    /**
     * List all members
     */
    public function index(Request $request)
    {
        $perPage = min($request->integer('per_page', 10), 50);

        $query = Member::query();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        $members = $query->orderByDesc('id')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $members->items(),
            'meta' => [
                'current_page' => $members->currentPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
                'last_page' => $members->lastPage(),
            ],
        ]);
    }

    /**
     * Create a member
     */
    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();

        // Check phone conflict with temples
        if (! empty($data['phone'])) {
            $templeExists = Temple::where('phone', $data['phone'])->exists();
            if ($templeExists) {
                return response()->json([
                    'status' => false,
                    'error' => 'Phone number is already registered as a temple.',
                ], 409);
            }
        }

        $member = $this->memberService->create($data);

        return response()->json([
            'status' => true,
            'message' => 'Member created successfully.',
            'data' => $member,
        ], 201);
    }

    /**
     * Show a single member
     */
    public function show($id)
    {
        $member = $this->memberService->findWithTrashed($id);

        if (! $member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $member,
        ]);
    }

    /**
     * Update member
     */
    public function update(UpdateMemberRequest $request, $id)
    {
        $member = Member::find($id);

        if (! $member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found.',
            ], 404);
        }

        $data = $request->validated();

        // Check phone conflict
        if (! empty($data['phone'])) {
            $memberExists = Member::where('phone', $data['phone'])
                ->where('id', '!=', $id)
                ->exists();

            if ($memberExists) {
                return response()->json([
                    'status' => false,
                    'error' => 'Phone number is already registered.',
                ], 409);
            }
        }

        $member = $this->memberService->update($member, $data);

        return response()->json([
            'status' => true,
            'message' => 'Member updated successfully.',
            'data' => $member,
        ]);
    }

    /**
     * Soft delete member
     */
    public function destroy($id)
    {
        $member = Member::find($id);

        if (! $member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found.',
            ], 404);
        }

        $this->memberService->delete($member);

        return response()->json([
            'status' => true,
            'message' => 'Member deleted successfully.',
        ]);
    }

    /**
     * Show trashed members
     */
    public function trashed(Request $request)
    {
        $perPage = min($request->integer('per_page', 10), 50);

        $query = Member::onlyTrashed();

        if ($request->has('temple_id')) {
            $query->where('temple_id', $request->temple_id);
        }

        $members = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $members->items(),
            'meta' => [
                'current_page' => $members->currentPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
                'last_page' => $members->lastPage(),
            ],
        ]);
    }

    /**
     * Restore member
     */
    public function restore($id)
    {
        $member = $this->memberService->findWithTrashed($id);

        if (! $member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found.',
            ], 404);
        }

        if (! $member->trashed()) {
            return response()->json([
                'status' => false,
                'message' => 'Member is not deleted.',
            ], 400);
        }

        $this->memberService->restore($member);

        return response()->json([
            'status' => true,
            'message' => 'Member restored successfully.',
        ]);
    }

    /**
     * Force delete
     */
    public function forceDelete($id)
    {
        $member = $this->memberService->findWithTrashed($id);

        if (! $member) {
            return response()->json([
                'status' => false,
                'message' => 'Member not found.',
            ], 404);
        }

        $this->memberService->forceDelete($member);

        return response()->json([
            'status' => true,
            'message' => 'Member permanently deleted.',
        ]);
    }
}
