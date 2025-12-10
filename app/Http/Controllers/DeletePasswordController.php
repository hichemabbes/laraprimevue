<?php

namespace App\Http\Controllers;

use App\Models\DeletePassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeletePasswordController extends Controller
{
    public function getPasswordStatus()
    {
        $passwordRecord = DeletePassword::first();

        return response()->json(['hasPassword' => (bool) $passwordRecord]);
    }

    public function setOrUpdatePassword(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! in_array('SuperAdmin', $user->roles->pluck('name')->toArray())) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'current_password' => 'required_if:has_password,true|string|nullable',
            'new_password' => 'required|string|min:8|confirmed',
            'has_password' => 'required|boolean',
        ]);

        $passwordRecord = DeletePassword::first();
        $hasPassword = (bool) $passwordRecord;

        if ($hasPassword && $request->current_password && ! $passwordRecord->verifyPassword($request->current_password)) {
            return response()->json(['errors' => ['current_password' => ['Mot de passe actuel incorrect']]], 422);
        }

        if (! $passwordRecord) {
            $passwordRecord = new DeletePassword;
        }

        // Utiliser la propriété directement pour bénéficier du mutateur
        $passwordRecord->password = $request->new_password;
        $passwordRecord->save();

        return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
    }

    public function deletePassword(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! in_array('SuperAdmin', $user->roles->pluck('name')->toArray())) {
            return response()->json(['message' => 'Accès non autorisé'], 403);
        }

        $request->validate([
            'current_password' => 'required|string',
        ]);

        $passwordRecord = DeletePassword::first();
        if (! $passwordRecord) {
            return response()->json(['message' => 'Aucun mot de passe à supprimer'], 404);
        }

        if (! $passwordRecord->verifyPassword($request->current_password)) {
            return response()->json(['errors' => ['current_password' => ['Mot de passe actuel incorrect']]], 422);
        }

        $passwordRecord->delete();

        return response()->json(['message' => 'Mot de passe supprimé avec succès']);
    }
}
