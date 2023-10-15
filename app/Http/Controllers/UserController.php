<?php

namespace App\Http\Controllers;

use DateTime;
use DateTimeInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request) {
        $datetime = new DateTime('2010-12-30 23:21:46');
        return response()->json([
            [
                'id' => 1,
                'verified' => true,
                'category' => 'ISOTERM',
                'date' => $datetime->format(DateTimeInterface::ATOM),
                'name' => [
                    'team' => 'Tim Elang',
                    'member' => 'Rudi Hermansyah',
                ],
                'institution' => [
                    'name' => 'ITB',
                    'instructor' => 'Surendro'
                ]
            ],
            [
                'id' => 2,
                'verified' => true,
                'category' => 'CRYSTAL',
                'date' => $datetime->format(DateTimeInterface::ATOM),
                'name' => [
                    'team' => 'Tim Rajawali',
                    'member' => 'Budi Suradi',
                ],
                'institution' => [
                    'name' => 'ITS',
                    'instructor' => 'Kridanto'
                ]
            ]
        ]);
    }
}
