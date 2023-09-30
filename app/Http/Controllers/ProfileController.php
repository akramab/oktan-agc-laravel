<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function updateOrCreate(Request $request) {
        $currentUser = auth()->user();

        $profileDocuments = [];
        if ($currentUser->competition_type == 'CRYSTAL') {

            if($request->hasFile('registration_document')) {
                $regDocPath = $request->file('registration_document')->store('profile_documents');
                $profileDocuments[] = [
                    'name' => 'registration',
                    'path' => $regDocPath
                ];
            }

            if($request->hasFile('payment_document')) {
                $payDocPath = $request->file('payment_document')->store('profile_documents');
                $profileDocuments[] = [
                    'name' => 'payment',
                    'path' => $payDocPath
                ];
            }
        } else if ($currentUser->competition_type == 'ISOTERM') {
            if($request->hasFile('abstract_1_document')) {
                $abs1DocPath = $request->file('abstract_1_document')->store('profile_documents');
                $profileDocuments[] = [
                    'name' => 'abstract_1_document',
                    'path' => $abs1DocPath
                ];
            }

            if($request->hasFile('abstract_2_document')) {
                $abs2DocPath = $request->file('abstract_2_document')->store('profile_documents');
                $profileDocuments[] = [
                    'name' => 'abstract_2_document',
                    'path' => $abs2DocPath
                ];
            }

            if($request->hasFile('work_1_document')) {
                $work1DocPath = $request->file('work_1_document')->store('profile_documents');
                $profileDocuments[] = [
                    'name' => 'work_1_document',
                    'path' => $work1DocPath
                ];
            }

            if($request->hasFile('work_2_document')) {
                $work2DocPath = $request->file('work_2_document')->store('profile_documents');
                $profileDocuments[] = [
                    'name' => 'work_2_document',
                    'path' => $work2DocPath
                ];
            }

            if($request->hasFile('unified_document')) {
                $uniDocPath = $request->file('unified_document')->store('profile_documents');
                $profileDocuments[] = [
                    'name' => 'unified_document',
                    'path' => $uniDocPath
                ];
            }
        }

        $profile = Profile::query()->updateOrCreate(
            [
                'user_id' => $currentUser->id,
            ],
            [
                'user_id' => $currentUser->id,
                'members_data' => json_encode($request->input('members_data')),
                'institution_data' => json_encode($request->input('institution_data')),
                'documents_data' => json_encode($profileDocuments),
            ]
        );

        return response()->json([
            'profile_id' => $profile->id,
            'message' => 'profile created or updated',
        ]);
    }
}
