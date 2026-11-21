<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\LogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminCouponController extends Controller
{
    public function index(): Response
    {
        $coupons = Coupon::withCount('usages')
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Coupons/Index', ['coupons' => $coupons]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Coupons/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), $this->rules(), $this->messages(), $this->attributes());

        $this->attachLimitCheck($validator);

        if ($validator->fails()) {
            return back()
                ->with('error', $validator->errors()->first());
        }

        $coupon = Coupon::create($validator->validated());
        // Record coupon creation in activity logs.
        new LogService()->log(
            'Created',
            $coupon,
            [],
            'Coupon created.',
            null,
            $coupon->getAttributes()
        );

        return redirect()->route('admin_coupons_index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon): Response
    {
        $coupon->loadCount('usages');

        return Inertia::render('Admin/Coupons/Edit', [
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'min_order_amount' => $coupon->min_order_amount,
                'max_discount' => $coupon->max_discount,
                'starts_at' => $coupon->starts_at,
                'expires_at' => $coupon->expires_at,
                'usage_limit' => $coupon->usage_limit,
                'per_customer_limit' => $coupon->per_customer_limit,
                'first_order_only' => $coupon->first_order_only,
                'is_active' => $coupon->is_active,
                'usages_count' => $coupon->usages_count,
            ],
        ]);
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $rules = $this->rules($coupon);

        $validator = Validator::make(
            $request->all(),
            $rules,
            $this->messages(),
            $this->attributes()
        );

        $this->attachLimitCheck($validator);

        if ($coupon->usages()->exists()) {
            $validator->after(function ($v) use ($request, $coupon) {
                if ($request->input('code') !== $coupon->code) {
                    $v->errors()->add(
                        'code',
                        'The coupon code cannot be changed after it has been used.'
                    );
                }

                if ($request->input('type') !== $coupon->type) {
                    $v->errors()->add(
                        'type',
                        'The coupon type cannot be changed after it has been used.'
                    );
                }

                if ((float) $request->input('value') !== (float) $coupon->value) {
                    $v->errors()->add(
                        'value',
                        'The discount value cannot be changed after it has been used.'
                    );
                }
            });
        }

        if ($validator->fails()) {
            return back()
                ->with('error', $validator->errors()->first());
        }

        // Get validated incoming data.
        $data = $validator->validated();

        // Apply incoming changes without saving yet.
        $coupon->fill($data);

        // Get only attributes that have actually changed.
        $changes = $coupon->getDirty();

        // Capture only the old values of changed attributes.
        $fromRecords = [];

        foreach ($changes as $attribute => $newValue) {
            $fromRecords[$attribute] = $coupon->getOriginal($attribute);
        }

        // Save the changes.
        $coupon->save();

        // Capture only the new values of changed attributes.
        $toRecords = $coupon->getChanges();

        // Record coupon update in activity logs.
        new LogService()->log(
            'Updated',
            $coupon,
            $data,
            "Updated coupon \"{$coupon->code}\"",
            $fromRecords,
            $toRecords
        );

        return redirect()
            ->route('admin_coupons_index')
            ->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
{
    if ($coupon->usages()->exists()) {
        return back()->with(
            'error',
            'This coupon has been used and cannot be deleted. Deactivate it instead.'
        );
    }

    // Capture coupon attributes before deletion.
    $fromRecords = $coupon->getAttributes();

    // Delete the coupon.
    $coupon->delete();

    // Record coupon deletion in activity logs.
    new LogService()->log(
        'Deleted',
        $coupon,
        [],
        "Deleted coupon \"{$coupon->code}\".",
        $fromRecords,
        null
    );

    return redirect()
        ->route('admin_coupons_index')
        ->with('success', 'Coupon deleted.');
}

    private function attachLimitCheck($validator): void
    {
        $validator->after(function ($v) {
            $total = $v->getData()['usage_limit'] ?? null;
            $perCustomer = $v->getData()['per_customer_limit'] ?? null;

            // Only enforce when both are provided
            if ($total === null || $total === '' || $perCustomer === null || $perCustomer === '') {
                return;
            }

            if ((int) $total < (int) $perCustomer) {
                $v->errors()->add(
                    'usage_limit',
                    'The total usage limit cannot be less than the per-customer limit.'
                );
            }
        });
    }

    /**
     * Validation rules shared by store() and update().
     * On update, the unique check ignores the current coupon's id.
     */
    private function rules(?Coupon $ignore = null): array
    {
        return [
            'code' => [
                'required', 'string', 'max:50', 'alpha_dash',
                Rule::unique('coupons', 'code')->ignore($ignore?->id),
            ],
            'type' => ['required', 'in:percent,fixed'],
            'value' => ['required', 'numeric', 'min:0.01'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_limit' => ['required', 'integer', 'min:1'],
            'first_order_only' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * User-friendly messages for each rule.
     */
    private function messages(): array
    {
        return [
            // Code
            'code.required' => 'Please enter a coupon code.',
            'code.string' => 'The coupon code must be text.',
            'code.max' => 'The coupon code is too long — keep it under 50 characters.',
            'code.alpha_dash' => 'Coupon codes may only contain letters, numbers, dashes, and underscores.',
            'code.unique' => 'This code is already in use — choose a different one.',

            // Type
            'type.required' => 'Please choose whether the discount is a percentage or a fixed amount.',
            'type.in' => 'The discount type must be either percent or fixed.',

            // Value
            'value.required' => 'Please enter the discount value.',
            'value.numeric' => 'The discount value must be a number.',
            'value.min' => 'The discount must be greater than zero.',

            // Min order
            'min_order_amount.numeric' => 'The minimum order amount must be a number.',
            'min_order_amount.min' => 'The minimum order amount cannot be negative.',

            // Max discount
            'max_discount.numeric' => 'The maximum discount must be a number.',
            'max_discount.min' => 'The maximum discount cannot be negative.',

            // Dates
            'starts_at.date' => 'The start date is not a valid date.',
            'expires_at.date' => 'The expiry date is not a valid date.',
            'expires_at.after' => 'The expiry date must be after the start date.',

            // Usage limit
            'usage_limit.integer' => 'The total usage limit must be a whole number.',
            'usage_limit.min' => 'The total usage limit must be at least 1.',

            // Per-customer limit
            'per_customer_limit.required' => 'Please set how many times a customer can use this coupon.',
            'per_customer_limit.integer' => 'The per-customer limit must be a whole number.',
            'per_customer_limit.min' => 'The per-customer limit must be at least 1.',

            // Flags
            'first_order_only.boolean' => 'The first-order-only flag must be true or false.',
            'is_active.boolean' => 'The active flag must be true or false.',
        ];
    }

    /**
     * Human names for fields — used by any rule whose custom message isn't defined.
     */
    private function attributes(): array
    {
        return [
            'code' => 'coupon code',
            'type' => 'discount type',
            'value' => 'discount value',
            'min_order_amount' => 'minimum order amount',
            'max_discount' => 'maximum discount',
            'starts_at' => 'start date',
            'expires_at' => 'expiry date',
            'usage_limit' => 'usage limit',
            'per_customer_limit' => 'per-customer limit',
            'first_order_only' => 'first order only',
            'is_active' => 'active status',
        ];
    }

    /**
     * Standard failure response: inline field errors + first error as toast.
     */
    private function validationErrorResponse($validator): RedirectResponse
    {
        return back()
            ->withErrors($validator)
            ->with('error', $validator->errors()->first())
            ->withInput();
    }
}
