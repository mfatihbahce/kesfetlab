<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Workshop;
use App\Models\ParentUser;
use App\Models\ParentStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class FormController extends Controller
{
    /**
     * Online form ana sayfası
     */
    public function index()
    {
        $workshops = Workshop::active()->get();
        
        return view('form.index', compact('workshops'));
    }

    /**
     * Form gönderimi
     */
    public function submit(Request $request)
    {
        // Validation rules
        $rules = Student::rules();
        $rules['workshop_id'] = 'required|exists:workshops,id';

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Öğrenci verilerini hazırla (workshop_id hariç)
            $studentData = $request->except(['workshop_id']);
            
            // Öğrenci kaydını oluştur
            $student = Student::create($studentData);

            // Atölye seçimi için enrollment oluştur
            $workshop = Workshop::find($request->workshop_id);
            
            // Enrollment tablosuna kayıt ekle
            $student->enrollments()->create([
                'workshop_id' => $workshop->id,
                'group_id' => null, // Henüz grup atanmamış
                'status' => 'pending',
                'enrollment_date' => now(),
                'start_date' => now(),
                'amount' => $workshop->price,
                'payment_status' => 'pending',
                'is_active' => true,
            ]);

            return redirect()->route('form.success')
                ->with('success', 'Öğrenci kaydınız başarıyla alınmıştır. En kısa sürede size dönüş yapılacaktır.');

        } catch (\Exception $e) {
            \Log::error('Form submission error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Kayıt oluşturulurken bir hata oluştu. Lütfen tekrar deneyiniz.')
                ->withInput();
        }
    }

    /**
     * Başarı sayfası
     */
    public function success()
    {
        return view('form.success');
    }
}
