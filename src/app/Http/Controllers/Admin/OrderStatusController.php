<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class OrderStatusController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/OrderStatuses/Index', [
            'statuses' => OrderStatus::with('nextStatuses:id,key,label')->ordered()->get(),
            'status' => session('status'),
            'error' => session('error'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/OrderStatuses/Create', [
            'statuses' => OrderStatus::ordered()->get(['id', 'key', 'label']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request, true);

        $orderStatus = DB::transaction(function () use ($data) {
            $orderStatus = new OrderStatus([
                'key' => $data['key'],
                'label' => $data['label'],
                'sort_order' => $data['sort_order'],
                'is_initial' => $data['is_initial'] ?? false,
                'is_void' => $data['is_void'] ?? false,
            ]);

            if ($orderStatus->is_initial) {
                OrderStatus::query()->update(['is_initial' => false]);
            }

            $orderStatus->save();
            $orderStatus->nextStatuses()->sync($data['next_status_ids'] ?? []);

            return $orderStatus;
        });

        return redirect()->route('admin.order-statuses.index')->with('status', "「{$orderStatus->label}」を登録しました。");
    }

    public function edit(OrderStatus $orderStatus): Response
    {
        return Inertia::render('Admin/OrderStatuses/Edit', [
            'orderStatus' => $orderStatus->load('nextStatuses:id,key,label'),
            'statuses' => OrderStatus::ordered()->get(['id', 'key', 'label']),
        ]);
    }

    public function update(Request $request, OrderStatus $orderStatus): RedirectResponse
    {
        $data = $this->validateData($request, false, $orderStatus);

        if (
            $orderStatus->is_initial
            && empty($data['is_initial'])
            && OrderStatus::where('is_initial', true)->count() <= 1
        ) {
            return back()->withErrors(['is_initial' => '少なくとも1つの状態を初期状態に設定してください。'])->withInput();
        }

        DB::transaction(function () use ($data, $orderStatus) {
            if (! empty($data['is_initial'])) {
                OrderStatus::query()->where('id', '!=', $orderStatus->id)->update(['is_initial' => false]);
            }

            $orderStatus->label = $data['label'];
            $orderStatus->sort_order = $data['sort_order'];
            $orderStatus->is_initial = $data['is_initial'] ?? false;
            $orderStatus->is_void = $data['is_void'] ?? false;
            $orderStatus->save();

            $orderStatus->nextStatuses()->sync($data['next_status_ids'] ?? []);
        });

        return redirect()->route('admin.order-statuses.index')->with('status', "「{$orderStatus->label}」を更新しました。");
    }

    public function destroy(OrderStatus $orderStatus): RedirectResponse
    {
        if (Order::where('status', $orderStatus->key)->exists()) {
            return back()->with('error', 'この状態を使用している注文が存在するため削除できません。');
        }

        if ($orderStatus->is_initial) {
            return back()->with('error', '初期状態に設定されているため削除できません。先に別の状態を初期状態にしてください。');
        }

        if (OrderStatus::count() <= 1) {
            return back()->with('error', '最後の状態は削除できません。');
        }

        $orderStatus->delete();

        return redirect()->route('admin.order-statuses.index')->with('status', "「{$orderStatus->label}」を削除しました。");
    }

    private function validateData(Request $request, bool $isCreate, ?OrderStatus $orderStatus = null): array
    {
        return $request->validate([
            'key' => $isCreate
                ? ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/', 'unique:order_statuses,key']
                : ['prohibited'],
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_initial' => ['boolean'],
            'is_void' => ['boolean'],
            'next_status_ids' => ['array'],
            'next_status_ids.*' => [
                'integer',
                Rule::exists('order_statuses', 'id')->whereNot('id', $orderStatus?->id ?? 0),
            ],
        ]);
    }
}
