<?php

namespace App\Http\Controllers;

use App\Models\User;
use DateTime;
use DateTimeInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request) {
        $datetime = new DateTime('2010-12-30 23:21:46');
        $users = User::query()
            ->get()
            ->where('role', '=', 'USER')
            ->all();
        $data = [];
        foreach($users as $user) {
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
                'date' => $datetime->format(DateTimeInterface::ATOM),
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
        return response()->json($data);
    }
}
