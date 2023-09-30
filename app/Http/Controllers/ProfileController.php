<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function updateOrCreate(Request $request): JsonResponse {
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
                    'id' => '1',
                    'name' => 'abstract',
                    'path' => $abs1DocPath
                ];
            }

            if($request->hasFile('abstract_2_document')) {
                $abs2DocPath = $request->file('abstract_2_document')->store('profile_documents');
                $profileDocuments[] = [
                    'id' => '2',
                    'name' => 'abstract',
                    'path' => $abs2DocPath
                ];
            }

            if($request->hasFile('work_1_document')) {
                $work1DocPath = $request->file('work_1_document')->store('profile_documents');
                $profileDocuments[] = [
                    'id' => '1',
                    'name' => 'work',
                    'path' => $work1DocPath
                ];
            }

            if($request->hasFile('work_2_document')) {
                $work2DocPath = $request->file('work_2_document')->store('profile_documents');
                $profileDocuments[] = [
                    'id' => '2',
                    'name' => 'work',
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
                'team' => $request->input('team'),
                'sub_theme' => $request->input('sub_theme'),
                'members_data' => $request->input('members_data'),
                'institution_data' => $request->input('institution_data'),
                'documents_data' => json_encode($profileDocuments),
            ]
        );

        return response()->json([
            'profile_id' => $profile->id,
            'message' => 'profile created or updated',
        ]);
    }

    public function get(): JsonResponse {
        $currentUser = auth()->user();

        $userProfile = Profile::query()
            ->where('id', $currentUser->id)
            ->first();

        if (isset($userProfile)) {
            $membersData = $userProfile->getMembersData();
            $institutionData = $userProfile->getInstitutionData();
            $documentsData = $userProfile->getDocumentsData();

            $membersDataResp = [];
            if(isset($membersData)) {
                // sort by id
                usort($membersData, function($a, $b){
                    return strcmp($a->id, $b->id);
                });
                $membersDataResp = $membersData;
            }

            $institutionDataResp = null;
            if(isset($institutionData)) {
                $institutionDataResp = $institutionData;
            }

            $documentsDataResp = [];
            if(isset($documentsData)) {
                $documentsDataResp = $documentsData;
            }

            return response()->json([
                'members' => $membersDataResp,
                'institution' => $institutionDataResp,
                'documents' => $documentsDataResp,
            ]);
        }

        return response()->json();
    }
}
