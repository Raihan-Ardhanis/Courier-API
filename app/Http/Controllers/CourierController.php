<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourierStoreRequest;
use App\Http\Requests\CourierUpdateRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['sometimes', 'string', 'max:255'],
            'level' => ['sometimes', 'string', 'regex:/^[1-5](,[1-5])*$/'],
            'sort' => ['sometimes', 'in:name,level,registered_at'],
            'direction' => ['sometimes', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Courier::query();

        if (isset($validated['search'])) {
            $terms = preg_split('/\s+/', trim($validated['search']), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($terms as $term) {
                $query->where('name', 'like', "%{$term}%");
            }
        }

        if (isset($validated['level'])) {
            $query->whereIn('level', array_map('intval', explode(',', $validated['level'])));
        }

        $sort = $validated['sort'] ?? 'name';
        $column = $sort === 'registered_at' ? 'created_at' : $sort;
        $defaultDirection = $sort === 'registered_at' ? 'desc' : 'asc';

        $query->orderBy($column, $validated['direction'] ?? $defaultDirection);

        $couriers = $query
            ->orderBy('id')
            ->paginate((int) ($validated['per_page'] ?? 15))
            ->withQueryString();

        return CourierResource::collection($couriers);
    }

    public function store(CourierStoreRequest $request)
    {
        $courier = Courier::create($request->validated());

        return (new CourierResource($courier))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Courier $courier)
    {
        return new CourierResource($courier);
    }

    public function update(CourierUpdateRequest $request, Courier $courier)
    {
        $courier->update($request->validated());

        return new CourierResource($courier->refresh());
    }

    public function destroy(Courier $courier)
    {
        $courier->delete();

        return response()->noContent();
    }
}
