<?php

namespace App\Http\Controllers;

use App\Models\Breed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BreedController extends Controller
{
    /**
     * Display the breeds management page.
     */
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();
        $breeds = Breed::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%");
                });
            })
            ->paginate(15)
            ->withQueryString();
        return view('breeds.index', compact('breeds', 'q'));
    }

    /**
     * Store a newly created breed in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:breeds,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $breed = Breed::create($request->only(['name']));

        return response()->json([
            'success' => true,
            'breed' => $breed,
            'message' => 'Irk başarıyla eklendi.'
        ]);
    }

    /**
     * Update the specified breed in storage.
     */
    public function update(Request $request, Breed $breed)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:breeds,name,' . $breed->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $breed->update($request->only(['name']));

        return response()->json([
            'success' => true,
            'breed' => $breed,
            'message' => 'Irk başarıyla güncellendi.'
        ]);
    }

    /**
     * Remove the specified breed from storage.
     */
    public function destroy(Breed $breed)
    {
        $this->authorize('delete-core'); // superadmin değilse 403 döner
        if ($breed->pets()->exists()) {
            return back()->withErrors('Bu ırk kullanımda, silinemez.');
        }
        $breed->delete();

        return response()->json([
            'success' => true,
            'message' => 'Irk başarıyla silindi.'
        ]);
    }
}
