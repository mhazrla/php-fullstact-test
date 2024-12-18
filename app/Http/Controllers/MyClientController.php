<?php

namespace App\Http\Controllers;

use App\Models\MyClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class MyClientController extends Controller
{
    public function store(Request $request): RedirectResponse
    {

        $request->validate([
            "name" => 'required|string|max:250',
            "slug" => 'required|string|max:100|unique:my_client',
            "is_project" => 'required|in:0,1',
            "self_capture" => 'required|in:0,1',
            "client_prefix" => 'required|string|size:4',
            "client_logo" => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            "address" => 'nullable|string',
            "phone_number" => 'nullable|string|max:50',
            "city" => 'nullable|string|max:50'
        ]);

        $client_logo = $request->file('client_logo') ? $request->file('client_logo')->store('client_logos', 's3') : 'no-image.jpg';

        $client = MyClient::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'client_logo' => $client_logo,
            'is_project' => $request->is_project ?? '0',
            'self_capture' => $request->self_capture ?? '1',
            'client_prefix' => $request->client_prefix,
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'city' => $request->city,
        ]);

        Redis::set('client:' . $client->slug, json_encode($client));

        return response()->json($client, 201);
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'name' => 'required|string|max:250',
            'slug' => 'required|string|max:100|unique:my_client',
            'client_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_project' => 'required|in:0,1',
            'self_capture' => 'required|in:0,1',
            "client_prefix" => 'required|string|size:4',
            'client_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50'
        ]);

        $client = MyClient::where('slug', $slug)->firstOrFail();

        Redis::del('client:' . $client->slug);

        if ($request->hasFile('client_logo')) {
            if ($client->client_logo !== 'no-image.jpg') {
                Storage::disk('s3')->delete($client->client_logo);
            }
            $client->client_logo = $request->file('client_logo')->store('client_logos', 's3');
        }

        $client->update($request->only([
            'name',
            'is_project',
            'self_capture',
            'client_prefix',
            'address',
            'phone_number',
            'city'
        ]));

        Redis::set('client:' . $client->slug, json_encode($client));

        return response()->json($client);
    }

    public function destroy($slug)
    {
        $client = MyClient::where('slug', $slug)->firstOrFail();

        $client->update(['deleted_at' => now()]);

        Redis::del('client:' . $client->slug);

        return response()->json(['message' => 'Client deleted successfully']);
    }

    public function show($slug)
    {
        $cachedClient = Redis::get('client:' . $slug);
        if ($cachedClient) {
            return response()->json(json_decode($cachedClient));
        }

        $client = MyClient::where('slug', $slug)->firstOrFail();

        Redis::set('client:' . $client->slug, json_encode($client));

        return response()->json($client);
    }
}
