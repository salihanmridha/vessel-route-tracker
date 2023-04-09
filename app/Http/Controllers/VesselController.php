<?php

namespace App\Http\Controllers;

use App\Http\Requests\VesselInfoRequest;
use App\Http\Requests\VesselMmsiPositionRequest;
use App\Http\Resources\VesselInfoResource;
use App\Http\Resources\VesselPositionResource;
use App\Http\Resources\VesselRouteResource;
use App\Models\VesselInfo;
use App\Traits\ResponseJson;
use App\Traits\Scrapper;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class VesselController extends Controller
{
    use ResponseJson, Scrapper;

    const VESSELS = 'https://www.vesselfinder.com/vessels/details/';
    const VESSELPOSITION = 'https://www.marinetraffic.com/en/ais/details/ships/';

    const MAGICPORTVESSELPOSITION = 'https://magicport.ai/vessels/';

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

    public function vesselRoute(VesselInfoRequest $request)
    {
        $url = self::VESSELS . $request->imoCode;
        $vesselRoute = $this->getVesselRoutes($url);

        if($vesselRoute instanceof JsonResponse || $vesselRoute == null){
            return response()->json([
                "status" => Response::HTTP_NOT_FOUND,
                "success" => false,
                "message" => "The IMO code that you entered is invalid. Please try again.",
                "data" => [],
            ]);
        }

        if (is_array($vesselRoute) && array_key_exists("callsign", $vesselRoute)){
            $data = [
                "departure_port" => $vesselRoute["departureport"] ?? null,
                "departure_atd" => $vesselRoute["departureatd"] ?? null,
                "callsign" => $vesselRoute["callsign"] ?? null,
                "flag" => $vesselRoute["flag"] ?? null,
                "length_beam" => $vesselRoute["lengthbeam"] ?? null,
                "imo_mmsi" => $vesselRoute["imommsi"] ?? null,
                "navigation_status" => $vesselRoute["navigationstatus"] ?? null,
                "current_draught" => $vesselRoute["currentdraught"] ?? null,
                "course_speed" => $vesselRoute["coursespeed"] ?? null,
                "arrival_port" => $vesselRoute["arrivalport"] ?? null,
                "arrival_atd" => $vesselRoute["arrivalatd"] ?? null,
                "latest_port_calls" => $vesselRoute["latest_port_calls"] ?? null,
            ];

            return $this->data(
                Response::HTTP_OK,
                true,
                "IMO Code $request->imoCode is valid",
                new VesselRouteResource($data));
        }

        response()->json([
            "status" => Response::HTTP_NOT_FOUND,
            "success" => false,
            "message" => "The IMO code that you entered is invalid. Please try again.",
            "data" => [],
        ]);


    }

    public function vesselPosition(VesselInfoRequest $request)
    {
        $url = self::VESSELPOSITION . $request->imoCode;

        $vesselPosition = $this->getVesselPosition($url);

        if($vesselPosition instanceof JsonResponse || $vesselPosition == null){
            return response()->json([
                "status" => Response::HTTP_NOT_FOUND,
                "success" => false,
                "message" => "The IMO Code that you entered does not exist in our database. Please try again.",
                "data" => [],
            ]);
        }

        if (is_array($vesselPosition) && array_key_exists("latitude_longitude", $vesselPosition)){
            $data = [
                "position_received" => $vesselPosition["position_received"] ?? null,
                "vessel_local_time" => $vesselPosition["vessel_local_time"] ?? null,
                "area" => $vesselPosition["area"] ?? null,
                "current_port" => $vesselPosition["current_port"] ?? null,
                "latitude_longitude" => $vesselPosition["latitude_longitude"] ?? null,
                "navigational_status" => $vesselPosition["navigational_status"] ?? null,
                "speed_course" => $vesselPosition["speed_course"] ?? null,
                "ais_source" => $vesselPosition["ais_source"] ?? null,
            ];

            return $this->data(
                Response::HTTP_OK,
                true,
                "IMO Code $request->imoCode is valid",
                new VesselPositionResource($data));
        }

        response()->json([
            "status" => Response::HTTP_NOT_FOUND,
            "success" => false,
            "message" => "The IMO code that you entered is invalid. Please try again.",
            "data" => [],
        ]);
    }

    public function vesselPositionByMmsi(VesselMmsiPositionRequest $request)
    {
        $url = self::MAGICPORTVESSELPOSITION . $request->mmsiCode;

        $vesselPosition = $this->getVesselPositionByMmsi($url);

        if($vesselPosition instanceof JsonResponse){
            return response()->json([
                "status" => Response::HTTP_NOT_FOUND,
                "success" => false,
                "message" => "The MMSI Code that you entered does not exist in our database. Please try again.",
                "data" => [],
            ]);
        }

        if (is_array($vesselPosition) && array_key_exists("latitude_longitude", $vesselPosition)){
            $data = [
                "destination" => $vesselPosition["destination"] ?? null,
                "reported_eta" => $vesselPosition["reported_eta"] ?? null,
                "speed" => $vesselPosition["speed"] ?? null,
                "heading" => $vesselPosition["heading"] ?? null,
                "draught" => $vesselPosition["draught"] ?? null,
                "position_received" => $vesselPosition["position_received"] ?? null,
                "latitude_longitude" => $vesselPosition["latitude_longitude"] ?? null,
                "navigational_status" => $vesselPosition["navigational_status"] ?? null,
            ];

            return $this->data(
                Response::HTTP_OK,
                true,
                "MMSI Code $request->mmsiCode is valid",
                new VesselPositionResource($data));
        }

        response()->json([
            "status" => Response::HTTP_NOT_FOUND,
            "success" => false,
            "message" => "The MMSI code that you entered is invalid. Please try again.",
            "data" => [],
        ]);
    }
}
