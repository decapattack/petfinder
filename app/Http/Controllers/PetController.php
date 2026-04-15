<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PetController extends Controller
{
    public function index()
    {
        $pets = Auth::user()->pets;
        return view('pets.index', compact('pets'));
    }

    public function create()
    {
        return view('pets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'               => 'required|string|max:255',
            'especie'            => 'required|string|max:100',
            'raca'               => 'required|string|max:100',
            'cor'                => 'required|string|max:100',
            'condicoes_especiais'=> 'nullable|string|max:500',
            'foto'               => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = $request->file('foto')->store('pets', 'public');

        $pet = Pet::create([
            'user_id' => Auth::id(),
            'nome' => $request->nome,
            'especie' => $request->especie,
            'raca' => $request->raca,
            'cor' => $request->cor,
            'condicoes_especiais' => $request->condicoes_especiais,
            'foto' => $path,
            'status' => 'seguro',
        ]);

        return redirect()->route('pets.index')->with('success', 'Pet cadastrado com sucesso!');
    }

    public function showPublic($uuid)
    {
        $pet = Pet::where('uuid', $uuid)->with('user')->firstOrFail();
        return view('pets.public', compact('pet'));
    }

    public function destroy(Pet $pet)
    {
        if ($pet->user_id !== Auth::id()) {
            abort(403);
        }

        if ($pet->foto) {
            Storage::disk('public')->delete($pet->foto);
        }

        $pet->delete();
        return back()->with('success', 'Pet removido com sucesso.');
    }
}
