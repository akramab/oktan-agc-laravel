<?php

namespace App\Http\Controllers;

use App\Models\User;
use DateTime;
use DateTimeInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use function Laravel\Prompts\error;

class UserController extends Controller
{
    public function index(Request $request) {
        $users = User::query()
            ->get()
            ->where('role', '=', 'USER')
            ->all();
        $data = [];
        foreach($users as $user) {
            if ($user->profile != null) {
                $institutionName = '';
                if ($user->competition_type == 'ISOTERM') {
                    $institutionName = $user->profile->getInstitutionData()->university->name;
                } else {
                    $institutionName = $user->profile->getInstitutionData()->school->name;
                }
                $institutionInstructor = '';
                if ($user->competition_type == 'ISOTERM') {
                    $institutionInstructor = $user->profile->getInstitutionData()->university->lecturer;
                } else {
                    $institutionInstructor = $user->profile->getInstitutionData()->teacher->name;
                }

                $data[] = [
                    'id' => $user->id,
                    'verified' => $user->is_payment_verified,
                    'category' => $user->competition_type,
                    'date' => $user->created_at->format(DateTimeInterface::ATOM),
                    'name' => [
                        'team' => $user->profile->team,
                        'member' => $user->profile->getMembersData()[0]->name,
                    ],
                    'institution' => [
                        'name' => $institutionName,
                        'instructor' => $institutionInstructor
                    ]
                ];
            }
        }
        return response()->json($data);
    }

    public function verifyPayment(Request $request) {
        auth()->user()->update([
            'is_payment_verified' => true,
        ]);

        return response()->json([
            'message' => "user payment verified succesfully",
        ]);
    }
}
