<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $hotels = Hotel::orderByDesc('id')->paginate(10);
            return response()->json([
                'data' => $hotels->items(),
                'links' => $hotels->links('pagination::bootstrap-5')->render()
            ]);
        }

        return view('hotels.index');
    }

    public function show(Hotel $hotel)
    {
        return response()->json($hotel);
    }

    public function store(Request $request)
    {
        return $this->saveHotel($request);
    }

    public function update(Request $request, Hotel $hotel)
    {
        return $this->saveHotel($request, $hotel);
    }

    protected function saveHotel(Request $request, Hotel $hotel = null)
    {
        $rules = [
            'display_name' => 'required|string|max:255',
            'name' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|max:5',
            'country_name' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'city_name' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'zip_code' => 'nullable|string|max:20',
            'star_rating' => 'nullable|numeric|min:0|max:5',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'room_count' => 'nullable|integer|min:0',
            'phone' => 'nullable|string|max:30',
            'fax' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'property_category' => 'nullable|string|max:255',
            'property_sub_category' => 'nullable|string|max:255',
            'chain_code' => 'nullable|string|max:255',
            'facilities' => 'nullable|string',
            'priority' => 'required|integer|min:0',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:2048',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        try {
            if ($request->hasFile('images')) {
                $storedImages = [];
                foreach ($request->file('images') as $file) {
                    $storedImages[] = $file->store('hotel_images', 'public');
                }
                $data['images'] = json_encode($storedImages);
            }

            if ($hotel) {
                $hotel->update($data);
                return response()->json(['message' => 'Hotel updated successfully.', 'hotel' => $hotel]);
            } else {
                $newHotel = Hotel::create($data);
                return response()->json(['message' => 'Hotel created successfully.', 'hotel' => $newHotel], 201);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server Error',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Hotel $hotel)
    {
        if ($hotel->images) {
            $paths = json_decode($hotel->images, true);
            if (is_array($paths)) {
                foreach ($paths as $path) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        $hotel->delete();
        return response()->json(['message' => 'Hotel deleted']);
    }

    public function bulkUpdate(Request $request)
    {
        $ids = json_decode($request->input('bulk_ids'), true);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'No hotels selected.'], 422);
        }

        $rules = [
            'display_name' => 'nullable|string|max:255',
            'city_name' => 'nullable|string|max:255',
            'country_name' => 'nullable|string|max:255',
            'priority' => 'nullable|integer|min:0',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = array_filter($validator->validated(), fn($v) => !is_null($v));

        if (empty($data)) {
            return response()->json(['message' => 'No fields to update.'], 422);
        }

        try {
            Hotel::whereIn('id', $ids)->update($data);
            return response()->json(['message' => 'Hotels updated successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server Error',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'No hotels selected for deletion.'], 422);
        }

        try {
            $hotels = Hotel::whereIn('id', $ids)->get();

            foreach ($hotels as $hotel) {
                if ($hotel->images) {
                    $paths = json_decode($hotel->images, true);
                    if (is_array($paths)) {
                        foreach ($paths as $path) {
                            Storage::disk('public')->delete($path);
                        }
                    }
                }
                $hotel->delete();
            }

            return response()->json(['message' => 'Selected hotels deleted successfully.']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Server error',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }
}
