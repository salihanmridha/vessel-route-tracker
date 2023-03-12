<?php

namespace App\Http\Controllers;

use App\Http\Requests\VesselInfoRequest;
use App\Http\Resources\VesselInfoResource;
use App\Models\VesselInfo;
use App\Traits\ResponseJson;
use App\Traits\Scrapper;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class VesselController extends Controller
{
    use ResponseJson, Scrapper;

    const VESSELS = 'https://www.vesselfinder.com/vessels/details/';

    public function vesselInfo(VesselInfoRequest $request)
    {

        $vessel = VesselInfo::firstWhere("imo_number", $request->imoCode);

        if ($vessel){
            return $this->data(
                Response::HTTP_OK,
                true,
                "IMO Code $request->imoCode is valid",
                new VesselInfoResource($vessel));
        }


        $url = self::VESSELS . $request->imoCode;
        $vesselInfo = $this->getVesselInfo($url);

        if($vesselInfo instanceof JsonResponse || $vesselInfo == null){
            return $vesselInfo;
        }

        if (is_array($vesselInfo) && array_key_exists("imonumber", $vesselInfo)){

            $vessel = VesselInfo::create([
                "imo_number" => $vesselInfo['imonumber'],
                "vessel_name" => $vesselInfo['vesselname'] ?? null,
                "ship_type" => $vesselInfo['shiptype'] ?? null,
                "flag" => $vesselInfo['flag'] ?? null,
                "gross_tonnage" => $vesselInfo['grosstonnage'] ?? null,
                "summer_deadweight_t" => $vesselInfo['summerdeadweightt'] ?? null,
                "length_overall_m" => $vesselInfo['lengthoverallm'] ?? null,
                "beam_m" => $vesselInfo['beamm'] ?? null,
                "year_of_built" => $vesselInfo['yearofbuilt'] ?? null,
            ]);
            return $this->data(
                Response::HTTP_OK,
                true,
                "IMO Code $request->imoCode is valid",
                new VesselInfoResource($vessel));
        }

        response()->json([
            "status" => Response::HTTP_NOT_FOUND,
            "success" => false,
            "message" => "The IMO Code that you entered does not exist in our database. Please try again.",
            "data" => [],
        ]);
    }
}
