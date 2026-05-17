<?php

namespace App\Http\Controllers;

use App\Models\BloodCar;
use Illuminate\Http\Request;

class BloodCarController extends Controller
{
    // عرض كل العربيات بتاعت المستشفى
    public function index($hospital_id)
    {
        $cars = BloodCar::where('hospital_id', $hospital_id)->get();
        return response()->json($cars);
    }

    // إضافة عربية جديدة
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hospital_id'  => 'required|exists:hospitals,id',
            'vehicle_name' => 'required|string',
            'driver_name'  => 'required|string',
            'status'       => 'in:available,on_mission',
        ]);

        $car = BloodCar::create($validated);
        return response()->json($car, 201);
    }

    // تعديل status العربية
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:available,on_mission'
        ]);

        $car = BloodCar::findOrFail($id);
        $car->update(['status' => $request->status]);
        return response()->json($car);
    }

    // حذف عربية
    public function destroy($id)
    {
        $car = BloodCar::findOrFail($id);
        $car->delete();
        return response()->json(['message' => 'Car deleted successfully']);
    }
}
