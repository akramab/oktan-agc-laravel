<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shuchkin\SimpleXLSXGen;
use function Laravel\Prompts\error;

class ProfileController extends Controller
{
    public function updateOrCreate(Request $request): JsonResponse {
        $currentUser = auth()->user();

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
            ]
        );

        if($request->hasFile('payment_document')) {
            $profile->addMediaFromRequest('payment_document')->toMediaCollection(Profile::PAYMENT_DOCUMENT);
        }

        if($request->hasFile('registration_document')) {
            $profile->addMediaFromRequest('registration_document')->toMediaCollection(Profile::CRYSTAL_REGISTRATION_DOCUMENT);
        }

        if($request->hasFile('abstract_1_document')) {
            $profile->addMediaFromRequest('abstract_1_document')->toMediaCollection(Profile::ISOTERM_ABSTRACT_1_DOCUMENT);
        }

        if($request->hasFile('abstract_2_document')) {
            $profile->addMediaFromRequest('abstract_2_document')->toMediaCollection(Profile::ISOTERM_ABSTRACT_2_DOCUMENT);
        }

        if($request->hasFile('work_1_document')) {
            $profile->addMediaFromRequest('work_1_document')->toMediaCollection(Profile::ISOTERM_WORK_1_DOCUMENT);
        }

        if($request->hasFile('work_2_document')) {
            $profile->addMediaFromRequest('work_2_document')->toMediaCollection(Profile::ISOTERM_WORK_2_DOCUMENT);
        }

        if($request->hasFile('unified_document')) {
            $profile->addMediaFromRequest('unified_document')->toMediaCollection(Profile::ISOTERM_UNIFIED_DOCUMENT);
        }

        return response()->json([
            'profile_id' => $profile->id,
            'message' => 'profile created or updated',
        ]);
    }

    public function get(): JsonResponse {
        $currentUser = auth()->user();

        $userProfile = Profile::query()
            ->where('user_id', $currentUser->id)
            ->first();

        if (isset($userProfile)) {
            $membersData = $userProfile->getMembersData();
            $institutionData = $userProfile->getInstitutionData();

            $documentsData = [];

            $paymentDocUrl = $userProfile->getFirstMediaUrl(Profile::PAYMENT_DOCUMENT);
            if ($paymentDocUrl != '') {
                $documentsData[] = [
                    'name' => 'payment',
                    'path' => $paymentDocUrl,
                ];
            }

            if($currentUser->competition_type == User::COMPETITION_CRYSTAL) {
                $regDocUrl = $userProfile->getFirstMediaUrl(Profile::CRYSTAL_REGISTRATION_DOCUMENT);
                if ($regDocUrl != '') {
                    $documentsData[] = [
                        'name' => 'registration',
                        'path' => $regDocUrl,
                    ];
                }
            } else if ($currentUser->competition_type == User::COMPETITION_ISOTERM) {
                $abs1DocUrl = $userProfile->getFirstMediaUrl(Profile::ISOTERM_ABSTRACT_1_DOCUMENT);
                if ($abs1DocUrl != '') {
                    $documentsData[] = [
                        'id' => '1',
                        'name' => 'abstract',
                        'path' => $abs1DocUrl,
                    ];
                }

                $abs2DocUrl = $userProfile->getFirstMediaUrl(Profile::ISOTERM_ABSTRACT_2_DOCUMENT);
                if ($abs2DocUrl != '') {
                    $documentsData[] = [
                        'id' => '2',
                        'name' => 'abstract',
                        'path' => $abs2DocUrl,
                    ];
                }

                $work1DocUrl = $userProfile->getFirstMediaUrl(Profile::ISOTERM_WORK_1_DOCUMENT);
                if ($work1DocUrl != '') {
                    $documentsData[] = [
                        'id' => '1',
                        'name' => 'work',
                        'path' => $work1DocUrl,
                    ];
                }

                $work2DocUrl = $userProfile->getFirstMediaUrl(Profile::ISOTERM_WORK_2_DOCUMENT);
                if ($work2DocUrl != '') {
                    $documentsData[] = [
                        'id' => '2',
                        'name' => 'work',
                        'path' => $work2DocUrl,
                    ];
                }

                $uniDocUrl = $userProfile->getFirstMediaUrl(Profile::ISOTERM_UNIFIED_DOCUMENT);
                if ($uniDocUrl != '') {
                    $documentsData[] = [
                        'name' => 'unified_document',
                        'path' => $uniDocUrl,
                    ];
                }
            }


            $institutionDataResp = null;
            if(isset($institutionData)) {
                $institutionDataResp = $institutionData;
            }

            $documentsDataResp = null;
            if(isset($documentsData)) {
                $documentsDataResp = $documentsData;
            }

            return response()->json(collect([
                'team' => $userProfile->team,
                'sub_theme' => $userProfile->sub_theme,
                'members' => $membersData,
                'institution' => $institutionDataResp,
                'documents' => $documentsDataResp,
            ])->filter());
        }

        return response()->json();
    }

    public function downloadDocument($id)
    {

        $currentUser = User::query()
            ->where('id', $id)
            ->first();

        $userProfile = Profile::query()
            ->where('user_id', $currentUser->id)
            ->first();

        $fileName =  uniqid("dokumen-",true). "-" . $currentUser->competition_type . "-" . $userProfile->team . '.zip';
        if ($currentUser->competition_type == User::COMPETITION_CRYSTAL) {
            $zip = new \ZipArchive();

            //TEST
            $membersData = $userProfile->getMembersData();
            $institutionData = $userProfile->getInstitutionData();

            $books = [
                [
                    'nama_team', 'subtema',
                    'universitas', 'dosen',
                    'nama_anggota_1', 'tahun_angkatan_anggota_1', 'email_anggota_1', 'jurusan_anggota_1', 'nomor_wa_anggota_1',
                    'nama_anggota_2', 'tahun_angkatan_anggota_2', 'email_anggota_2', 'jurusan_anggota_2', 'nomor_wa_anggota_2',
                    'nama_anggota_3', 'tahun_angkatan_anggota_3', 'email_anggota_3', 'jurusan_anggota_3', 'nomor_wa_anggota_3'
                ],
                [
                    $userProfile->team, $userProfile->sub_theme,
                    $institutionData['university']['name'], $institutionData['university']['lecturer'],
                    $membersData[0]->name, $membersData[0]->year, $membersData[0]->email, $membersData[0]->major, $membersData[0]->wa_number,
                    $membersData[1]->name, $membersData[1]->year, $membersData[1]->email, $membersData[1]->major, $membersData[1]->wa_number,
                    $membersData[2]->name, $membersData[2]->year, $membersData[2]->email, $membersData[2]->major, $membersData[2]->wa_number,
                ],
            ];
            $xlsx = SimpleXLSXGen::fromArray( $books );
            $xlsx->saveAs(public_path('books.xlsx'));

            if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
            {
                $zip->addFile(public_path('books.xlsx'), 'books.xlsx');
                $zip->close();
            }
            // END OF TEST

            $regDoc = $userProfile->getFirstMedia(Profile::CRYSTAL_REGISTRATION_DOCUMENT);
            if ($regDoc != null) {
                $path =  explode('/',$regDoc->getPath());

                $documentsData[] = [
                    'name' => 'registration',
                    'path' => end($path),
                ];

                if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                {
                    $relativeName = basename(end($path));
                    $zip->addFile($regDoc->getPath(), $relativeName);
                    $zip->close();
                }
            }

            $regDoc = $userProfile->getFirstMedia(Profile::PAYMENT_DOCUMENT);
            if ($regDoc != null) {
                $path =  explode('/',$regDoc->getPath());

                $documentsData[] = [
                    'name' => 'payment',
                    'path' => end($path),
                ];

                if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                {
                    $relativeName = basename(end($path));
                    $zip->addFile($regDoc->getPath(), $relativeName);
                    $zip->close();
                }
            }
        } else if ($currentUser->competition_type == User::COMPETITION_ISOTERM) {
            $zip = new \ZipArchive();

            $regDoc = $userProfile->getFirstMedia(Profile::ISOTERM_ABSTRACT_1_DOCUMENT);
            if ($regDoc != null) {
                $path =  explode('/',$regDoc->getPath());

                $documentsData[] = [
                    'name' => 'abstract_1',
                    'path' => end($path),
                ];

                if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                {
                    $relativeName = basename(end($path));
                    $zip->addFile($regDoc->getPath(), $relativeName);
                    $zip->close();
                }
            }

            $regDoc = $userProfile->getFirstMedia(Profile::PAYMENT_DOCUMENT);
            if ($regDoc != null) {
                $path =  explode('/',$regDoc->getPath());

                $documentsData[] = [
                    'name' => 'payment',
                    'path' => end($path),
                ];

                if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                {
                    $relativeName = basename(end($path));
                    $zip->addFile($regDoc->getPath(), $relativeName);
                    $zip->close();
                }
            }

            $regDoc = $userProfile->getFirstMedia(Profile::ISOTERM_UNIFIED_DOCUMENT);
            if ($regDoc != null) {
                $path =  explode('/',$regDoc->getPath());

                $documentsData[] = [
                    'name' => 'unified',
                    'path' => end($path),
                ];

                if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                {
                    $relativeName = basename(end($path));
                    $zip->addFile($regDoc->getPath(), $relativeName);
                    $zip->close();
                }
            }
        }

        return response()->download(public_path($fileName));
    }

    public function downloadAllDocuments()
    {

        $currentUsers = User::query()
            ->get();

        $globalZip = new \ZipArchive();
        $globalFileName = uniqid("dokumen-",true). "-alldocs" ;

        foreach($currentUsers as $currentUser) {
            $userProfile = Profile::query()
            ->where('user_id', $currentUser->id)
            ->first();

            if ($userProfile != null) {
                $fileName =  uniqid("dokumen-",true). "-" . $currentUser->competition_type . "-" . $userProfile->team . '.zip';
                if ($currentUser->competition_type == User::COMPETITION_CRYSTAL) {
                    $zip = new \ZipArchive();

                    $regDoc = $userProfile->getFirstMedia(Profile::CRYSTAL_REGISTRATION_DOCUMENT);
                    if ($regDoc != null) {
                        $path =  explode('/',$regDoc->getPath());

                        $documentsData[] = [
                            'name' => 'registration',
                            'path' => end($path),
                        ];

                        if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                        {
                            $relativeName = basename(end($path));
                            $zip->addFile($regDoc->getPath(), $relativeName);
                            $zip->close();
                        }
                    }

                    $regDoc = $userProfile->getFirstMedia(Profile::PAYMENT_DOCUMENT);
                    if ($regDoc != null) {
                        $path =  explode('/',$regDoc->getPath());

                        $documentsData[] = [
                            'name' => 'payment',
                            'path' => end($path),
                        ];

                        if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                        {
                            $relativeName = basename(end($path));
                            $zip->addFile($regDoc->getPath(), $relativeName);
                            $zip->close();
                        }
                    }
                } else if ($currentUser->competition_type == User::COMPETITION_ISOTERM) {
                    $zip = new \ZipArchive();

                    $regDoc = $userProfile->getFirstMedia(Profile::ISOTERM_ABSTRACT_1_DOCUMENT);
                    if ($regDoc != null) {
                        $path =  explode('/',$regDoc->getPath());

                        $documentsData[] = [
                            'name' => 'abstract_1',
                            'path' => end($path),
                        ];

                        if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                        {
                            $relativeName = basename(end($path));
                            $zip->addFile($regDoc->getPath(), $relativeName);
                            $zip->close();
                        }
                    }

                    $regDoc = $userProfile->getFirstMedia(Profile::PAYMENT_DOCUMENT);
                    if ($regDoc != null) {
                        $path =  explode('/',$regDoc->getPath());

                        $documentsData[] = [
                            'name' => 'payment',
                            'path' => end($path),
                        ];

                        if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                        {
                            $relativeName = basename(end($path));
                            $zip->addFile($regDoc->getPath(), $relativeName);
                            $zip->close();
                        }
                    }

                    $regDoc = $userProfile->getFirstMedia(Profile::ISOTERM_UNIFIED_DOCUMENT);
                    if ($regDoc != null) {
                        $path =  explode('/',$regDoc->getPath());

                        $documentsData[] = [
                            'name' => 'unified',
                            'path' => end($path),
                        ];

                        if ($zip->open(public_path($fileName), \ZipArchive::CREATE)== TRUE)
                        {
                            $relativeName = basename(end($path));
                            $zip->addFile($regDoc->getPath(), $relativeName);
                            $zip->close();
                        }
                    }
                }

                if ($zip->open(public_path($globalFileName), \ZipArchive::CREATE)== TRUE)
                {
                    $relativeName = basename(end($path));
                    $zip->addFile(public_path($fileName), $fileName);
                    $zip->close();
                }
            }
        }

        return response()->download(public_path($globalFileName));
    }
}
