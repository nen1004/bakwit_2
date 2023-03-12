<?php

namespace App\Http\Controllers;

use App\Http\Requests\Evacuee\UpdateEvacueeRequest;
use App\Models\Barangay;
use App\Models\EvacuationCenter;
use App\Models\EvacuationCenterType;
use App\Models\Evacuee;
use App\Services\EvacuationCenterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvacuationCenterController extends Controller
{
    /**
     * @var EvacuationCenterService
     */
    private $evacuationCenterService;

    /**
     * @param EvacuationCenterService $evacuationCenterService
     */
    public function __construct(EvacuationCenterService $evacuationCenterService)
    {
        $this->evacuationCenterService = $evacuationCenterService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $barangayIds = [];
        $storedBarangay = EvacuationCenter::select('barangay_id')->get();
        foreach ($storedBarangay as $brgy) {
            $barangayIds[] = $brgy->barangay_id;
        }

        $evacuationCenters = $this->evacuationCenterService->centers($request)->paginate();
        $barangays = Barangay::whereNotIn('id', $barangayIds)->orderBy('name', 'asc')->get();
        return view('pages.evacuation_center.index', [
            'barangays' => $barangays,
            'evacuation_center_types' => EvacuationCenterType::all(),
            'evacuationCenters' => $evacuationCenters,
            'allBarangay' => Barangay::orderBy('name', 'asc')->get(),
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $evacuationCenter = $this->evacuationCenterService->store($request->all());
            if($request->hasFile('files')) {
                $this->storeFiles($evacuationCenter, $request);
            }
        } catch (\Exception $exception) {
            return redirect()->back()->with('msg', 'Error when saving. Please try again or contact administrator.');
        }
        return redirect()->back()->with('msg', 'Evacuation Center is added!');
    }

    /**
     * @param $evacuationCenter
     * @param $request
     * @return void
     */
    private function storeFiles($evacuationCenter, $request): void
    {
        $files = $request->file('files');
        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            $filePath = $file->storePubliclyAs('public_uploads/'.$evacuationCenter->id, $filename, 'public');
            $evacuationCenter->files()->create([
                'name' => $filename,
                'path' => $filePath,
            ]);
        }
    }

    /**
     * @param EvacuationCenter $bdrrmo
     * @return JsonResponse
     */
    public function getCenter(EvacuationCenter $bdrrmo): JsonResponse
    {
        $data = [
            'max_capacity' => $bdrrmo->max_capacity,
            'barangay' => $bdrrmo->barangay_id,
            'center_type' => $bdrrmo->evacuation_center_type_id,
            'evacuee' => $bdrrmo->evacuee,
            'files' => $bdrrmo->files,
        ];
        return response()->json($data);
    }

    /**
     * @param EvacuationCenter $bdrrmo
     * @param Request $request
     * @return void
     */
    public function update(EvacuationCenter $bdrrmo, Request $request)
    {
        $evacuees = $request->only([
            'family_count',
            'male_count',
            'female_count',
            'pwd_count',
        ]);

        if ($bdrrmo->evacuee()->count() > 0) {
            $subTotal = intval($request->male_count) + intval($request->female_count);
            $isFull = $subTotal >= intval($request->max_capacity) ? true : false;
            $bdrrmo->update([
                'evacuation_center_type_id' => $request->evacuation_center_type_id,
                'is_evacuation_center_full' => $isFull,
            ]);
            $bdrrmo->evacuee()->update($evacuees);
        } else {
            $this->storeEvacueesCounts($bdrrmo->id, $evacuees);
        }

        if($request->hasFile('files')) {
            $this->deleteFileDirectory($bdrrmo->id);
            $this->updateFiles($bdrrmo, $request);
        }

        return redirect()->back()->with('msg', 'Evacuation Center is modified!');
    }

    /**
     * @param $evacuationId
     * @param $evacuees
     * @return void
     */
    private function storeEvacueesCounts($evacuationId, $evacuees): void
    {
        Evacuee::create([
            'evacuation_center_id' => $evacuationId,
            'family_count' => $evacuees['family_count'],
            'male_count' => $evacuees['male_count'],
            'female_count' => $evacuees['female_count'],
            'pwd_count' => $evacuees['pwd_count'],
        ]);
    }

    /**
     * @param $evacuation
     * @param $request
     * @return void
     */
    private function updateFiles($evacuation, $request): void
    {
        $files = $request->file('files');
        foreach ($files as $file) {
            $filename = $file->getClientOriginalName();
            $filePath = $file->storePubliclyAs('public_uploads/'.$evacuation->id, $filename, 'public');
            $evacuation->files()->update([
                'name' => $filename,
                'path' => $filePath,
            ]);
        }
    }

    /**
     * @param EvacuationCenter $bdrrmo
     * @return RedirectResponse
     */
    public function destroy(EvacuationCenter $bdrrmo)
    {
        $this->deleteFileDirectory($bdrrmo->id);
        $bdrrmo->delete();
        return redirect()->back()->with('msg', 'Evacuation center successfully deleted.');
    }

    /**
     * @param $evacuationId
     * @return void
     */
    private function deleteFileDirectory($evacuationId): void
    {
        $filePath = '/public/public_uploads/'.$evacuationId;
        Storage::deleteDirectory($filePath);
    }
}
